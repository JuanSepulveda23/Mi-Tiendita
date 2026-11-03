# Reglas Obligatorias del Proyecto Mi-Tiendita

Cualquier agente que trabaje en este proyecto **debe acatar de forma irrestricta** los estándares definidos en la skill [.agents/skills/mi-tiendita-standards/SKILL.md](.agents/skills/mi-tiendita-standards/SKILL.md).

## Mandatos Primordiales

1. **Arquitectura y Servicios**:
   - No colocar lógica compleja de negocio en los controladores. Delegar a la capa `App\Services`.
   - Modificaciones de stock en ventas deben usar bloqueo pesimista: `Product::where(...)->lockForUpdate()->firstOrFail()` dentro de `DB::transaction()`.
   - Inmutabilidad de costo: Todo item vendido (`SaleItem`) debe almacenar su costo histórico (`cost_price`). Nunca calcular márgenes pasados con el costo actual del producto.
   - Todo campo en migraciones debe estar validado en los `FormRequest` y presente en el formulario `_form.blade.php`.

2. **Flujo de Trabajo y Calidad**:
   - No hacer commits directos a `main`. Usar ramas descriptivas (`feature/*`, `fix/*`, `refactor/*`).
   - Usar *Conventional Commits* (`feat:`, `fix:`, `refactor:`, `test:`, `security:`).
   - Siempre correr y asegurar que pasen las pruebas (`php artisan test`) y el formateador (`vendor/bin/pint --test`) antes de completar una tarea.
   - Nueva funcionalidad exige nuevas pruebas automatizadas en `tests/`.

3. **Protección de Datos y Seguridad**:
   - No dejar `/register` abierto al público general en producción; aplicar RBAC (roles y permisos mínimos).
   - Nunca concatenar parámetros en consultas SQL crudas.
   - Prohibido ejecutar consultas dentro de bucles PHP (`N+1 queries`); usar `GROUP BY` y agregaciones de base de datos.
   - Respetar normas de protección de datos (GDPR / Habeas Data): consentimiento explícito, preservación fiscal con Soft Deletes y cero PII en logs.

