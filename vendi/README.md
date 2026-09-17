# Vendi POS — nueva plataforma comercial

Este directorio inicia la especificación independiente de **Vendi POS**.

## Objetivo

Construir una plataforma propia para PyMEs que conecte el flujo completo:

**Venta → Pedido → Pago → Inventario → Facturación → Envío → Cliente → Personal**

La implementación comercial nueva debe mantenerse separada del código propietario heredado existente en el repositorio.

## Módulos iniciales

### Vendi POS
- Ventas de mostrador
- Ventas simultáneas
- Cantidades decimales
- Calculadora de cantidades
- Pagos múltiples
- Caja y cortes
- Clientes
- Inventario multi-sucursal

### Vendi Pedidos
- Pedidos de mostrador
- Pedidos provenientes de WhatsApp
- Estados: nuevo, confirmado, preparando, listo, enviado, entregado y cancelado
- Reserva de inventario
- Historial completo por pedido

### Vendi Envíos
Arquitectura desacoplada mediante proveedores:
- Skydropx
- Pakke
- futuros proveedores

Funciones:
- cotización
- generación de guía
- tracking
- costo real de envío
- asociación venta/pedido/envío

### Vendi WhatsApp
- Conversaciones vinculadas a clientes
- Conversión de conversación a pedido
- Confirmaciones de pedido
- Envío de ticket/factura
- Notificaciones de envío y tracking

### Vendi Factura
El POS no actuará como PAC. Se diseñará una capa fiscal que pueda conectarse a proveedores autorizados.

Funciones previstas:
- CFDI 4.0
- perfiles fiscales de clientes
- datos fiscales por producto
- timbrado mediante API
- XML y representación PDF
- autofacturación mediante QR/código de ticket
- cancelaciones y consulta de estatus
- preparación de factura global
- registro de UUID y trazabilidad

La integración fiscal se abstraerá mediante una interfaz de proveedor para evitar dependencia directa de un PAC.

### Vendi Personal
Primera fase:
- empleados
- horarios
- asistencias
- retardos
- faltas
- préstamos
- bonos
- comisiones
- cálculo de pago estimado

La nómina fiscal se implementará posteriormente como módulo especializado.

## Principios técnicos

- PHP 8.x
- MySQL/MariaDB
- responsive y touch-first
- multiempresa
- multisucursal
- roles y permisos
- bitácora/auditoría
- CSRF
- consultas preparadas/ORM
- credenciales y llaves cifradas
- integraciones desacopladas
- migraciones de base de datos
- API preparada para ecommerce y apps

## Arquitectura de integraciones

Cada servicio externo debe implementarse detrás de una interfaz propia.

Ejemplos:

```
ShippingProvider
 ├─ SkydropxProvider
 └─ PakkeProvider

MessagingProvider
 └─ WhatsAppProvider

FiscalProvider
 └─ PacProvider

EcommerceProvider
 ├─ ShopifyProvider
 └─ WooCommerceProvider
```

## Modelo conceptual fiscal

Cada negocio podrá configurar:
- RFC
- razón social
- régimen fiscal
- código postal fiscal
- certificados de sello digital
- series/folios
- proveedor de timbrado

Cada producto podrá almacenar:
- clave de producto/servicio SAT
- clave de unidad SAT
- objeto de impuesto
- impuestos aplicables

Cada CFDI estará relacionado con la venta/pedido original y conservará su estado e historial.

## Próximos pasos

1. Crear esquema de base de datos propio.
2. Crear autenticación y modelo multiempresa/multisucursal.
3. Diseñar nueva interfaz Vendi.
4. Implementar núcleo POS e inventario.
5. Implementar pedidos.
6. Crear interfaces de integraciones.
7. Implementar Vendi Factura con entorno de pruebas de un proveedor fiscal.
8. Integrar WhatsApp y envíos.
9. Implementar Vendi Personal.
10. Preparar documentación, inventario de dependencias y versionado para la versión comercial.
