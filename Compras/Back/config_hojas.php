<?php

// config_hojas.php
return [
  'CargaCompras_US' => [
    'pais' => 'US',
    'columnas' => [
      'OrdenCompra'        => 'A',
      'Cliente'            => 'B',
      'NumArticulo'        => 'C',
      'CodItem'            => 'D',
      'Descripcion'        => 'E',
      'Cantidad_Abierta'    => 'F',
      'Precio'             => 'G',
      'ImportePendiente'            => 'H',
      'FechaContabilizacion'    => 'J',
      'FechaEntrega' => 'K',
      'Titular' => 'L',
    ],
    'tabla_bd' => 'compras_cargacompras'
  ],
  'CargaCompras_PA' => [
    'pais' => 'PA',
    'columnas' => [
      'OrdenCompra'        => 'A',
      'Cliente'            => 'B',
      'NumArticulo'        => 'C',
      'CodItem'            => 'D',
      'Descripcion'        => 'E',
      'Almacen'            => 'F',
      'Cantidad_Abierta'    => 'G',
      'Precio'             => 'H',
      'ImportePendiente'            => 'I',
      'FechaContabilizacion'    => 'K',
      'FechaEntrega' => 'L',
      'Titular' => 'M',
    ],
    'tabla_bd' => 'compras_cargacompras'
  ],
  'CargaCompras_GT' => [
    'pais' => 'GT',
    'columnas' => [
      'OrdenCompra'        => 'A',
      'Cliente'            => 'B',
      'NumArticulo'        => 'C',
      'CodItem'            => 'D',
      'Descripcion'        => 'E',
      'Almacen'            => 'F',
      'Cantidad_Abierta'    => 'G',
      'Precio'             => 'H',
      'ImportePendiente'            => 'I',
      'FechaContabilizacion'    => 'K',
      'FechaEntrega' => 'L',
      'Titular' => 'M',
    ],
    'tabla_bd' => 'compras_cargacompras'
  ],
  'CargaCompras_MEX' => [
    'pais' => 'MX',
    'columnas' => [
      'OrdenCompra'        => 'A',
      'Cliente'            => 'B',
      'NumArticulo'        => 'C',
      'CodItem'            => 'D',
      'Descripcion'        => 'E',
      'Almacen' => 'G',
      'Cantidad_Abierta' => 'H',
      'Precio' => 'I',
      'ImportePendiente' => 'J',
      'FechaContabilizacion' => 'L',
      'FechaEntrega' => 'M',
      'OrdenVenta' => 'N',
      'Titular' => 'Q',
    ],
    'tabla_bd' => 'compras_cargacompras'
  ],
  
  'CargaCompras_GDL' => [
    'pais' => 'GDL',
    'columnas' => [
      'OrdenCompra'        => 'A',
      'Cliente'            => 'B',
      'NumArticulo'        => 'C',
      'CodItem'            => 'D',
      'Descripcion'        => 'E',
      'Cantidad_Abierta' => 'F',
      'Precio' => 'G',
      'ImportePendiente' => 'H',
      'FechaContabilizacion' => 'J',
      'FechaEntrega' => 'K',
      'OrdenVenta' => 'L',
      'Titular' => 'M',
    ],
    'tabla_bd' => 'compras_cargacompras'
  ]
];
