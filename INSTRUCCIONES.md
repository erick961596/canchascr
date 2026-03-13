# SuperCancha — Parche 3: ONVO, Logs, Plans fix

## Pasos para aplicar

### 1. Copiar archivos (reemplazar los existentes)
```bash
cp -r supercancha-diff2/* tu-proyecto-laravel/
```

### 2. Correr migraciones
```bash
php artisan migrate
```
Crea dos tablas nuevas:
- `system_logs` — todos los logs del sistema
- Agrega columna `onvopay_price_id` a `plans`

### 3. Configurar el Price ID en los planes
- Ir a **ONVO Pay dashboard → Productos → Precios**
- Copiar el Price ID (ej: `cmklig02j32j5js200psne5v6`)
- En el admin `/admin/planes`, editar cada plan y pegar el Price ID

---

## Qué cambia

### ONVO Pay — flujo correcto
- El flow ahora usa `/v1/subscriptions` (no payment-intents)
- Se crea el customer en ONVO automáticamente si no existe
- El JS SDK renderiza el widget de pago dentro del modal
- El webhook activa la suscripción cuando ONVO confirma el pago
- Con SINPE → queda `pending` hasta aprobación manual del admin

### Sistema de logs
- Nueva tabla `system_logs` con niveles: info, auth, payment, subscription, warning, error
- `LogService` con helpers estáticos: `LogService::payment(...)`, `LogService::auth(...)`, etc.
- Admin puede ver logs en `/admin/logs`, filtrar por nivel, tipo, fecha, texto
- Click en "👁" muestra el contexto JSON completo
- Botón para limpiar logs de más de 30 días

### Planes — campo ONVO Price ID
- Campo `onvopay_price_id` en la tabla `plans`
- Se muestra en el formulario de edición del admin
- Si el plan no tiene price ID configurado, el botón de tarjeta muestra aviso

### Fix del modal de planes
- El UUID bug ya estaba resuelto en el parche anterior, acá se incluye actualizado con el campo nuevo
