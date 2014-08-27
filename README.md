woocommerce-conekta-oxxo
========================
This plugin adds Conekta OXXO as payment gateway in WooCommerce plugin
Plugin Name: WooCommerce Conekta Oxxo
Plugin URI: https://github.com/ramelp/woocommerce-conekta-oxxo
Author: ramelp
Author URI: http://sisnodo.com
Version: 0.1
License: GPLv2 or later 


== Description ==

El plugin agrega una nueva pasarela de pago en [WooCommerce](http://wordpress.org/extend/plugins/woocommerce/) plugin.

Permite generar el cargo con Conekta en Pesos Mexicanos "MXN" con los siguientes parámetros:

"amount"        valor "Monto Total"
"currency"      valor "MXN",
"description"   valor "Recibo de pago para orden #" concatenando el no. de orden
"reference_id"  valor "No. de Orden"

Una ves generado el cargo se agregan a la orden las siguientes variables:

ckta-id              ID asignado por Conekta
ckta-creado          Fecha del cargo  
ckta-expira          Fecha de expiración del cargo
ckta-barcode         Clave del Código de Barras
ckta-barcodeurl      URL para generar el Código de Barras


== Installation ==

= Minimum Requirements =

* Tested in WordPress 3.9.2
* PHP version 5.3 or greater
* WooCommerce 2.1 or greater
*Cuenta en [Conekta](https://www.conekta.io/) 


*Cargar el plugin en la carpeta plugins o instalarlo usando WordPress Plugins Añadir Nuevo;
*Activar el plugin;
*Navegar en WooCommerce -> Ajustes -> Payment Gateways, seleccionar Pago Conekta OXXO.
  *Agregar Conekta Private key
  


== Changelog ==

= 0.1 =
* Versión Inicial del Plugin
