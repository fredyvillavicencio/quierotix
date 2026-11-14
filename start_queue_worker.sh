#!/bin/bash

# Script para iniciar el worker de colas de Laravel
# Este script debe ejecutarse para procesar trabajos en segundo plano como la generación de PDFs

echo "Iniciando worker de colas de Laravel..."
echo "Presiona Ctrl+C para detener"

# Navegar al directorio del proyecto
cd "$(dirname "$0")"

# Iniciar el worker de colas
# --queue=default: procesa la cola 'default'
# --tries=3: intenta procesar cada trabajo 3 veces antes de marcarlo como fallido
# --timeout=3600: tiempo máximo de ejecución por trabajo (1 hora)
# --sleep=3: espera 3 segundos entre trabajos cuando la cola está vacía
php artisan queue:work --queue=default --tries=3 --timeout=3600 --sleep=3