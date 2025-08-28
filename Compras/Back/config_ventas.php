<?php

// config_hojas.php
return [
  'CargaVentas_US' => [
    'pais' => 'US',
    'columnas' => [
      'OrdenCompra' => 'C',
      'Cliente'            => 'B',
      'NumArticulo'        => 'D',
      'Descripcion'        => 'E',
      'Cantidad_Abierta'    => 'I',
      'Precio'             => 'G',
      'Moneda' => 'H',
      'FechaContabilizacion'    => 'L',
      'Titular' => 'K',
      'FechaVencimiento' => 'M',
    ],
    'tabla_bd' => 'compras_cargaventas'
  ],
  'CargaVentas_PA' => [
    'pais' => 'PA',
    'columnas' => [
      'OrdenCompra' => 'C',
      'Cliente'            => 'B',
      'NumArticulo'        => 'D',
      'Descripcion'        => 'E',
      'Almacen' => 'F',
      'Moneda' => 'H',
      'Cantidad_Abierta'    => 'J',
      'Precio'             => 'I',
      'FechaContabilizacion'    => 'O',
      'Titular' => 'N',
      'FechaVencimiento' => 'P',
    ],
    'tabla_bd' => 'compras_cargaventas'
  ],

  'CargaVentas_GT' => [
    'pais' => 'GT',
    'columnas' => [
      'OrdenCompra' => 'C',
      'Cliente'            => 'B',
      'NumArticulo'        => 'D',
      'Descripcion'        => 'E',
      'Almacen' => 'F',
      'Moneda' => 'H',
      'Cantidad_Abierta'    => 'J',
      'Precio'             => 'I',
      'FechaContabilizacion'    => 'O',
      'Titular' => 'N',
      'FechaVencimiento' => 'P',
    ],
    'tabla_bd' => 'compras_cargaventas'
  ],

  'CargaVentas_MEX' => [
    'pais' => 'MX',
    'columnas' => [
      'OrdenCompra' => 'C',
      'Cliente'            => 'B',
      'NumArticulo'        => 'D',
      'Descripcion'        => 'E',
      'Almacen' => 'F',
      'Moneda' => 'H',
      'Cantidad_Abierta'    => 'J',
      'Precio'             => 'I',
      'FechaContabilizacion'    => 'P',
      'Titular' => 'O',
      'FechaVencimiento' => 'Q',
    ],
    'tabla_bd' => 'compras_cargaventas'
  ],

];
