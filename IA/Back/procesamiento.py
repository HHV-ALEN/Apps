import pandas as pd
import mysql.connector
import matplotlib.pyplot as plt
import seaborn as sns
import os
from datetime import datetime
import sys
import io

# Force UTF-8 encoding
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

def procesar(nombre_archivo):
    print(f"✅ Procesando el archivo recibido: {nombre_archivo}")
    # input("Presiona Enter para continuar...")

    print("🔌 Conectando a la base de datos...")
    db_config = {
        'host': 'localhost',
        'user': 'root',
        'password': '',  
        'database': 'alenapps'
    }

    try:
        conn = mysql.connector.connect(**db_config)
        print("✅ Conexión a la base de datos exitosa.")
        query = "SELECT * FROM ia_incidencias"
        df = pd.read_sql(query, conn)
        print(f"📊 {len(df)} registros obtenidos desde ia_incidencias.")
        conn.close()
    except Exception as e:
        print("❌ Error al conectar a la base de datos:", e)
        exit()

    os.makedirs("resultados_ia", exist_ok=True)

    print("🧹 Limpiando y formateando datos...")
    df['Fecha'] = pd.to_datetime(df['Fecha'])
    df['Mes'] = df['Fecha'].dt.month
    df['Anio'] = df['Fecha'].dt.year
    print("✅ Fechas formateadas correctamente.")

    output_folder = os.path.join(os.path.dirname(__file__), "resultados_ia")
    print(f"📁 Guardando resultados en: {output_folder}")
    if not os.path.exists(output_folder):
        os.makedirs(output_folder)

    print("📈 Generando gráfica: Incidentes por Mes...")
    plt.figure(figsize=(10,6))
    df.groupby(['Anio','Mes']).size().plot(kind='bar', color='skyblue')
    plt.title('Numero de Incidentes por Mes')
    plt.ylabel('Cantidad')
    plt.tight_layout()
    plt.savefig(f"{output_folder}/incidentes_por_mes.png")
    plt.close()
    print("✅ Guardado: incidentes_por_mes.png")

    print("📈 Generando gráfica: Top 10 Tipos de Incidentes...")
    plt.figure(figsize=(10,6))
    df['Incidente'].value_counts().head(10).plot(kind='barh', color='orange')
    plt.title('Top 10 Tipos de Incidentes')
    plt.xlabel('Cantidad')
    plt.tight_layout()
    plt.savefig(f"{output_folder}/top_incidentes.png")
    plt.close()
    print("✅ Guardado: top_incidentes.png")

    print("📈 Generando gráfica: Prioridades de Incidentes...")
    plt.figure(figsize=(8,6))
    sns.countplot(data=df, x='Prioridad', order=df['Prioridad'].value_counts().index, palette='Set2')
    plt.title('Incidentes por Prioridad')
    plt.tight_layout()
    plt.savefig(f"{output_folder}/prioridades.png")
    plt.close()
    print("✅ Guardado: prioridades.png")

    print("📈 Generando gráfica: Estatus (pie chart)...")
    plt.figure(figsize=(8,6))
    df['Estatus'].value_counts().plot.pie(autopct='%1.1f%%', startangle=90, shadow=True)
    plt.title('Distribución por Estatus')
    plt.ylabel('')
    plt.tight_layout()
    plt.savefig(f"{output_folder}/estatus_pie.png")
    plt.close()
    print("✅ Guardado: estatus_pie.png")

    print("🎉 ¡Análisis completado con éxito!")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("❗ No se proporcionó archivo como argumento.")
        sys.exit(1)

    archivo = sys.argv[1]
    procesar(archivo)
