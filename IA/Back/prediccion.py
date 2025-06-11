import pandas as pd
import matplotlib.pyplot as plt
from sklearn.linear_model import LinearRegression
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
import numpy as np
import mysql.connector
import os
import io
import sys

# Force UTF-8 encoding
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')


# 1. Configuración de la BD
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',  # tu contraseña
    'database': 'alenapps'
}

# 2. Crear carpeta resultados si no existe
output_folder = 'resultados_ia'
if not os.path.exists(output_folder):
    os.makedirs(output_folder)

# 3. Conectar y consultar datos
try:
    conn = mysql.connector.connect(**db_config)

    query_regresion = "SELECT Fecha FROM ia_incidencias WHERE Fecha IS NOT NULL"
    df = pd.read_sql(query_regresion, conn)

    query_tipos = "SELECT Fecha, Incidente FROM ia_incidencias WHERE Fecha IS NOT NULL AND Incidente IS NOT NULL"
    df_tipos = pd.read_sql(query_tipos, conn)

    conn.close()
except Exception as e:
    print("Error al conectar a la base de datos:", e)
    exit()
    
    
# ---------------------------
# PREDICCIÓN DE CANTIDAD
# --------------------------- 1.- Convertir Fechas a formato Pandas

df['Fecha'] = pd.to_datetime(df['Fecha'])
df_grouped = df.groupby(df['Fecha'].dt.to_period('M')).size().reset_index(name='Total_Incidentes')
df_grouped['Fecha'] = df_grouped['Fecha'].dt.to_timestamp()
df_grouped['Mes_Num'] = np.arange(len(df_grouped))
# - Agrupar registros por mes, contando cuantos incidentes hay en cada uno

# Modelo de regresión
X = df_grouped[['Mes_Num']] # Mes como número
y = df_grouped['Total_Incidentes'] # Incidentes x mes
modelo = LinearRegression()
modelo.fit(X, y)   # <- Creación y entrenamiento del modelo de regreción lineal

# Predicción de próximos 3 meses
futuro = pd.DataFrame({'Mes_Num': np.arange(len(df_grouped), len(df_grouped)+3)})
predicciones = modelo.predict(futuro)
# Genera predicciones para los próximos 3 meses (usando los siguientes valores de Mes_Num).


# ---------------------------
# GRAFICAR PREDICCIÓN
# ---------------------------
plt.figure(figsize=(12, 6))
plt.plot(df_grouped['Fecha'], y, label='Histórico', marker='o', color='blue')
fechas_futuras = pd.date_range(start=df_grouped['Fecha'].max(), periods=4, freq='M')[1:]
plt.plot(fechas_futuras, predicciones, label='Prediccion', marker='x', linestyle='--', color='green')
# Se crean las fechas futuras que corresponden a las predicciones, 1 por mes

# Anotar predicciones
for i, valor in enumerate(predicciones):
    plt.text(fechas_futuras[i], valor + 0.5, f'{int(valor)}', ha='center', fontsize=9, color='green')

plt.title('📊 Prediccion Mensual de Incidentes')
plt.xlabel('Fecha')
plt.ylabel('Cantidad de Incidentes')
plt.xticks(rotation=45)
plt.legend()
plt.grid(True)
plt.tight_layout()
plt.savefig(f'{output_folder}/prediccion_incidentes.png')
plt.close()

# ---------------------------
# PREDICCIÓN DEL TIPO
# ---------------------------

# Convierte fechas y las transforma en números para cada mes (ordinal = número único por mes)
df_tipos['Fecha'] = pd.to_datetime(df_tipos['Fecha'])
df_tipos['Mes'] = df_tipos['Fecha'].dt.to_period('M').apply(lambda r: r.ordinal)

le = LabelEncoder()
df_tipos['Incidente_Codificado'] = le.fit_transform(df_tipos['Incidente'])
# Codifica los tipos de incidentes (textos como "Conectividad", "Hardware", etc.) en números para que el modelo los entienda.

X_tipo = df_tipos[['Mes']]
y_tipo = df_tipos['Incidente_Codificado']

# Entrena un modelo de bosques aleatorios (RandomForest) para predecir qué tipo de incidente es más probable en el siguiente mes.
modelo_clasificacion = RandomForestClassifier()
modelo_clasificacion.fit(X_tipo, y_tipo)

mes_futuro = df_tipos['Mes'].max() + 1
pred_tipo = modelo_clasificacion.predict([[mes_futuro]])[0]
tipo_predicho = le.inverse_transform([pred_tipo])[0]
# Predice el tipo de incidente para el próximo mes y lo convierte de nuevo a texto

# ---------------------------
# GENERAR DESCRIPCIÓN DE RESULTADOS
# ---------------------------

# Detecta el mes con más incidentes.
mes_mas_incidentes = df_grouped.loc[df_grouped['Total_Incidentes'].idxmax()]
promedio_mensual = round(df_grouped['Total_Incidentes'].mean(), 2)
total_incidentes = df_grouped['Total_Incidentes'].sum()

descripcion = f"""
🔍 Análisis de Incidentes
-------------------------------
📅 Total de registros históricos: {total_incidentes}
📈 Promedio mensual: {promedio_mensual} incidentes
🔥 Mes con más incidentes: {mes_mas_incidentes['Fecha'].strftime('%B %Y')} con {mes_mas_incidentes['Total_Incidentes']} incidentes
🔮 Tipo más probable de incidente para el próximo mes: {tipo_predicho}

La gráfica muestra el comportamiento histórico mensual de los incidentes registrados. Las cruces verdes representan la predicción para los próximos 3 meses basada en una regresión lineal.
"""

# Guardar descripción en archivo de texto
with open(f"{output_folder}/descripcion_prediccion.txt", "w", encoding='utf-8') as f:
    f.write(descripcion.strip())

print("Prediccion generada con éxito.")

# ------------------------------------------------------------------------------------------------------------------------------------------------------
# PREDICCIÓN DE CATEGORÍAS MÁS FRECUENTES - 2da Predicción
# ---------------------------

# Agrupar por mes y tipo de incidente
df_tipos['Mes_Letra'] = df_tipos['Fecha'].dt.strftime('%Y-%m')
frecuencia_categorias = df_tipos.groupby(['Mes_Letra', 'Incidente']).size().reset_index(name='Conteo')

# Pivot para tener una matriz de categorías por mes
df_pivot = frecuencia_categorias.pivot(index='Mes_Letra', columns='Incidente', values='Conteo').fillna(0)

# Preparamos datos para modelo (últimos 6 meses)
df_ultimos = df_pivot.tail(6)  # Últimos 6 meses
# Aseguramos que las columnas sean categorías
X_cat = np.arange(len(df_ultimos)).reshape(-1, 1)

# Guardamos predicciones
predicciones_categoria = {}

for categoria in df_ultimos.columns:
    y_cat = df_ultimos[categoria].values
    if np.count_nonzero(y_cat) > 1:  # Para evitar errores por falta de datos
        modelo_cat = LinearRegression()
        modelo_cat.fit(X_cat, y_cat)
        pred = modelo_cat.predict([[len(df_ultimos)]])[0]
        predicciones_categoria[categoria] = pred
    else:
        predicciones_categoria[categoria] = 0

# Ordenar predicciones descendente
pred_cat_ordenado = sorted(predicciones_categoria.items(), key=lambda x: x[1], reverse=True)

# Tomar el top 5
top_categorias = pred_cat_ordenado[:5]
categorias = [x[0] for x in top_categorias]
valores = [x[1] for x in top_categorias]

print("-------- Predicción de categorías más frecuentes:     -----------------")
for categoria, valor in top_categorias:
    print(f"{categoria}: {valor:.1f} incidentes estimados")
    


# ---------------------------
# GRAFICAR CATEGORÍAS PREDICHAS
# ---------------------------
plt.figure(figsize=(10, 6))
bars = plt.bar(categorias, valores, color='skyblue')
plt.title('🔮 Prediccion de Categorías Más Frecuentes (Incidentes)')
plt.ylabel('Cantidad Estimada')
plt.xlabel('Categoría')
plt.xticks(rotation=45)
plt.grid(axis='y')

# Agregar valores encima
for bar in bars:
    yval = bar.get_height()
    plt.text(bar.get_x() + bar.get_width()/2.0, yval + 0.3, f'{yval:.1f}', ha='center', va='bottom')

plt.tight_layout()
plt.savefig(f"{output_folder}/prediccion_categorias.png")
plt.close()

# ---------------------------
# GENERAR TEXTO DE DESCRIPCIÓN
# ---------------------------

descripcion_categorias = "🔍 Prediccion de Categorías Más Frecuentes\n"
descripcion_categorias += "----------------------------------------------\n"
descripcion_categorias += f"Se analizaron las categorías de incidentes reportadas en los últimos 6 meses.\n"
descripcion_categorias += "Con base en esta información, se estimaron las categorías con mayor probabilidad de ocurrencia en el próximo mes:\n\n"

for i, (cat, val) in enumerate(top_categorias, 1):
    descripcion_categorias += f"{i}. {cat}: {val:.1f} incidentes estimados\n"

descripcion_categorias += "\nLa gráfica generada muestra las 5 categorías más probables de incidentes para el siguiente mes.\n"

with open(f"{output_folder}/descripcion_categorias.txt", "w", encoding='utf-8') as f:
    f.write(descripcion_categorias.strip())

print("Prediccion de categorías generada con éxito.")


