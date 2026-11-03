---
name: mi-tiendita-standards
description: >-
  Reglas primordiales de arquitectura, flujo de trabajo obligatorio, protección de datos y seguridad para el proyecto Mi-Tiendita.
  Debe ser aplicada obligatoriamente cada vez que se cree o modifique código, rutas, controladores, migraciones, modelos, pruebas o infraestructura en este repositorio.
---

# Estándares Obligatorios del Proyecto: Mi-Tiendita

Esta skill define las **reglas inquebrantables** y el **flujo de trabajo obligatorio** para el desarrollo, refactorización y mantenimiento del proyecto **Mi-Tiendita**.

> [!IMPORTANT]
> **Mandato para el Agente:** Sin importar la naturaleza o brevedad del prompt del usuario, el agente **DEBE** evaluar y adaptar cualquier solución de código para cumplir estrictamente con los 5 pilares definidos a continuación. Queda terminantemente prohibido generar soluciones que introduzcan atajos que vulneren la integridad de los datos, la seguridad o la arquitectura.

---

## 1. Reglas Primordiales de Arquitectura

### 1.1. Capa de Servicios (*Service Layer*) - No a los *Fat Controllers*
* Los controladores (`Http/Controllers`) **solo deben orquestar**: recibir peticiones, llamar a la capa de servicios o modelos, y retornar vistas o respuestas JSON.
* **Toda la lógica de negocio debe residir en `App\Services`**:
  * `SaleService`: Manejo transaccional de ventas, facturación y reservas.
  * `InventoryIntelligenceService`: Cálculo de rotación, proyección de agotamiento y sugerencias de compra.
  * `ExpirationAlertService`: Verificación de lotes, cálculo de días restantes y ciclo de vida de alertas.
  * `ProfitReportService`: Cálculos consolidados de márgenes y reportes contables.

### 1.2. Integridad Concurrente de Inventario (*Pessimistic Locking*)
* **Prohibido modificar stock sin bloqueo pesimista**: En operaciones de venta o ajuste de inventario donde el stock deba validarse y decrementarse, se debe utilizar `lockForUpdate()` dentro de una transacción `DB::transaction()`:
  ```php
  DB::transaction(function () use ($items) {
      foreach ($items as $item) {
          $product = Product::where('id', $item['product_id'])
              ->lockForUpdate()
              ->firstOrFail();

          if ($product->stock < $item['quantity']) {
              throw new \DomainException("Stock insuficiente para: {$product->name}");
          }

          $product->decrement('stock', $item['quantity']);
      }
  });
  ```

### 1.3. Inmutabilidad Contable (Costo Histórico de Venta)
* **Regla Contable**: La ganancia o margen de una venta jamás debe calcularse usando el precio de compra actual de la tabla `products`.
* La tabla `sale_items` **debe registrar siempre** el costo unitario al momento de la venta (`cost_price` / `purchase_price`).
* Toda consulta de rentabilidad histórica debe operar sobre `sale_items.price - sale_items.cost_price`.

### 1.4. Consistencia Integral de Dominio (Migración ↔ Request ↔ Vista)
* Ningún campo en la base de datos debe quedar desconectado:
  1. Si un atributo existe en la migración (ej. `expiration_date`, `entry_date`, `min_stock`), debe estar en `$fillable` y `$casts` del Modelo.
  2. **Debe** contar con reglas de validación explícitas en su respectivo `FormRequest` (`StoreProductRequest`, `UpdateProductRequest`).
  3. **Debe** contar con el input correspondiente en la vista Blade (`_form.blade.php`).

### 1.5. Agnosticismo de Base de Datos y Portabilidad
* Queda prohibido el uso de funciones exclusivas de un motor (ej. `FIELD()` de MySQL en `AlertController`).
* Las consultas deben ser compatibles con **MySQL y SQLite** (este último utilizado en las pruebas unitarias y en memoria). Usar construcciones estándar como:
  ```php
  ->orderByRaw("CASE risk_level WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
  ```

### 1.6. Programación Obligatoria de Tareas (*Task Scheduling*)
* Todo comando de consola batch (`Console/Commands`) que gestione alertas o sincronizaciones (ej. `app:check-expirations`) debe estar registrado en el programador de tareas en `routes/console.php`.

---

## 2. Flujo de Trabajo Obligatorio (Git & DevOps)

Todo cambio de código en este repositorio debe seguir el siguiente flujo riguroso:

### 2.1. Estrategia de Ramas (*Branching*)
* **Prohibido realizar commits directos a `main`**.
* Nomenclatura obligatoria de ramas:
  * `feature/<modulo>-<descripcion-corta>` (ej. `feature/pos-pessimistic-lock`)
  * `fix/<modulo>-<descripcion-corta>` (ej. `fix/expiration-date-validation`)
  * `refactor/<modulo>-<descripcion-corta>` (ej. `refactor/extract-sale-service`)
  * `test/<modulo>` (ej. `test/sales-coverage`)

### 2.2. Convención de Commits (*Conventional Commits*)
* Los mensajes de commit deben seguir el estándar:
  ```text
  <tipo>(<alcance>): <descripción concisa en imperativo>
  ```
  * `feat`: Nueva funcionalidad.
  * `fix`: Corrección de bug.
  * `refactor`: Cambio de código que ni arregla bug ni añade funcionalidad.
  * `test`: Adición o corrección de pruebas.
  * `security`: Parches o mejoras de seguridad.
  * `perf`: Mejoras de rendimiento.
  * `chore`: Mantenimiento de configuración, paquetes o dependencias.

### 2.3. Checklist Pre-Merge / Pre-Push Obligatorio
Antes de dar por completada una tarea o realizar push, el agente debe verificar:
1. **Pruebas Automatizadas**: Ejecutar y garantizar código de salida 0:
   ```bash
   php artisan test
   ```
2. **Formateo de Código**: Ejecutar Pint para apegarse al estándar PSR-12:
   ```bash
   vendor/bin/pint --test
   ```
3. **Compilación de Assets**:
   ```bash
   npm run build
   ```
4. **Cero Warnings de Codificación**: Archivos guardados exclusivamente en UTF-8 sin BOM (evitar caracteres mojibake).

---

## 3. Normas Internacionales de Protección de Datos (GDPR, Habeas Data)

### 3.1. Principio de Confidencialidad y Control de Registro
* **Registro de usuarios no público**: La ruta `/register` no debe permanecer abierta de manera indiscriminada en producción. Las cuentas deben crearse mediante invitación o por un Administrador.
* **Aislamiento de Información**: Ningún usuario debe tener visibilidad de costos de proveedores o balances globales a menos que tenga el rol de autorización correspondiente.

### 3.2. Consentimiento Informado
* Todo formulario donde se capture información de usuarios o clientes debe incorporar:
  * Enlace visible a la Política de Privacidad y Tratamiento de Datos.
  * Checkbox obligatorio de aceptación (*opt-in* no premarcado).

### 3.3. Trazabilidad Contable vs. Cancelación de Datos (Derechos ARCO)
* **Soft Deletes**: Cuando un usuario o cliente solicite la eliminación de sus datos, las transacciones de venta pasadas no deben eliminarse en cascada (para cumplir con retención fiscal). La referencia de usuario debe anonimizarse o preservarse mediante *Soft Deletes*.
* **Prohibido loguear PII**: No registrar datos sensibles (contraseñas, tokens, documentos de identidad) en `storage/logs/laravel.log`.

---

## 4. Prácticas de Desarrollo y Seguridad (OWASP & Hardening)

### 4.1. Control de Acceso Basado en Roles (RBAC)
* Todo endpoint protegido por `auth` debe además validar el rol o permiso del usuario (`RoleMiddleware` o Laravel Gates/Policies):
  * **Dueño / Administrador**: Acceso a configuración, reportes de margen, modificación de precios y proveedores.
  * **Cajero**: Acceso restringido al POS, registro rápido de venta y consulta de stock del día.

### 4.2. Prevención de Vulnerabilidades Web (OWASP Top 10)
* **Inyección SQL**: Utilizar siempre *Eloquent* o parámetros vinculados con *bindings* de PDO. Prohibido concatenar variables en `whereRaw()`, `selectRaw()` o `orderByRaw()`.
* **Protección CSRF**: Todas las rutas POST, PUT, PATCH, DELETE en `routes/web.php` deben incluir el token CSRF (`@csrf` en Blade o cabecera `X-CSRF-TOKEN` en Axios/Fetch).
* **Cabeceras de Seguridad HTTP**: La configuración de Nginx o middleware de Laravel debe forzar:
  * `X-Frame-Options: SAMEORIGIN`
  * `X-Content-Type-Options: nosniff`
  * `Referrer-Policy: strict-origin-when-cross-origin`
  * `Permissions-Policy: geolocation=(), camera=(), microphone=()`

### 4.3. Seguridad en Entornos e Infraestructura Docker
* **MySQL**: El puerto `3306` en `docker-compose.yml` debe vincularse a `127.0.0.1:3306:3306` o eliminarse de los puertos públicos expuestos.
* **Credenciales**: Nunca usar claves por defecto (`root`/`root`) en entornos que no sean puramente de test descartable; inyectar siempre mediante variables de `.env`.

---

## 5. Rendimiento y Escalabilidad

### 5.1. Prohibición de Consultas N+1 y Bucles con Queries
* Queda terminantemente prohibido ejecutar consultas a base de datos dentro de bucles PHP (`for`, `foreach`, `while`).
* Para cálculos temporales o métricas consolidadas (ej. ganancias por día en `ReportController`), se debe utilizar una única consulta agregada con `GROUP BY DATE(created_at)`.

### 5.2. Idempotencia en Comandos Batch
* Todo proceso por lotes debe ser idempotente: si se ejecuta múltiples veces en el mismo día (ej. `CheckExpirations`), no debe duplicar registros de alerta. Debe validar previamente con `firstOrCreate`, `updateOrCreate` o comprobar `$existingAlert`.

---

## 6. Protocolo de Adaptación de Prompts (Instrucción de Ejecución)

Siempre que recibas un requerimiento del usuario:
1. **Analiza el alcance**: Identifica si afecta modelos, controladores, vistas, migraciones o infraestructura.
2. **Contrasta contra estas reglas**:
   - ¿Requiere control de concurrencia? Aplica `lockForUpdate()`.
   - ¿Afecta precios/ganancias? Garantiza el guardado de costo histórico.
   - ¿Añade campos? Actualiza Migración + Modelo + FormRequest + Vista.
   - ¿Crea una consulta con agregación temporal? Escribe un único `GROUP BY`, sin bucles PHP.
   - ¿Es una nueva función? Escribe su prueba automatizada correspondiente.
3. **Aplica y comunica**: Resuelve el prompt respetando todas estas normas y explica brevemente en tu respuesta final cómo se salvaguardaron los estándares de seguridad, concurrencia o integridad del proyecto.

