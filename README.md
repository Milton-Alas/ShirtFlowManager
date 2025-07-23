# 👕 ShirtFlowManager

**Sistema de Gestión Integral para Negocios de Venta de Camisas**

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/Filament-3.2-orange?style=for-the-badge&logo=filament" alt="Filament">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

## 📋 Descripción

ShirtFlowManager es una aplicación web completa desarrollada con **Laravel 12** y **Filament 3** que permite gestionar de manera eficiente un negocio de venta de camisas. El sistema proporciona herramientas para el control de inventario, gestión de ventas, seguimiento de gastos y análisis financiero.

## ✨ Características Principales

### 🎯 Gestión de Productos
- **Catálogo de Productos**: Administración completa de camisas y productos
- **Variantes**: Control de colores, tallas y combinaciones
- **Inventario Inteligente**: Seguimiento en tiempo real del stock
- **Estados**: Productos activos/inactivos

### 👥 Gestión de Clientes
- **Base de Datos de Clientes**: Información completa de contacto
- **Clientes Frecuentes**: Identificación y seguimiento especial
- **Historial de Compras**: Registro completo de transacciones
- **Notas y Observaciones**: Información adicional personalizada

### 💰 Sistema de Ventas
- **Generación Automática de Números de Venta**: Formato `V-YYYYMMDD-XXXX`
- **Múltiples Métodos de Pago**: Efectivo, transferencia, etc.
- **Descuentos y Promociones**: Sistema flexible de descuentos

### 📊 Control Financiero
- **Registro de Gastos**: Categorización y seguimiento de gastos operativos
- **Reportes Financieros**: Dashboard con métricas clave
- **Análisis de Rentabilidad**: Seguimiento de márgenes y ganancias
- **Widgets Informativos**: Visualización de datos en tiempo real

### 📈 Dashboard y Reportes
- **Resumen Financiero**: Ingresos, gastos y utilidades
- **Análisis de Ventas**: Productos más vendidos y tendencias
- **Métricas por Tallas**: Análisis de preferencias de clientes
- **Gráficos Interactivos**: Visualización clara de datos

## 🛠️ Tecnologías Utilizadas

- **Backend**: Laravel 12.x
- **Frontend Admin**: Filament 3.2
- **Base de Datos**: MySQL/SQLite
- **PHP**: 8.2+
- **Interfaz**: Blade Templates con Tailwind CSS
- **Testing**: PHPUnit

## 📦 Instalación

### Prerrequisitos

- PHP 8.2 o superior
- Composer
- Node.js y NPM
- MySQL o SQLite

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/Milton-Alas/ShirtFlowManager.git
   cd ShirtFlowManager
   ```

2. **Instalar dependencias de PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Node.js**
   ```bash
   npm install
   ```

4. **Configurar el archivo de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar la base de datos**
   - Editar `.env` con tus credenciales de base de datos
   - Para SQLite (desarrollo):
     ```bash
     touch database/database.sqlite
     ```

6. **Ejecutar migraciones**
   ```bash
   php artisan migrate
   ```

7. **Crear usuario administrador**
   ```bash
   php artisan make:filament-user
   ```

8. **Compilar assets**
   ```bash
   npm run build
   ```

9. **Iniciar el servidor**
   ```bash
   php artisan serve
   ```

## 🚀 Uso Rápido

### Desarrollo con Script Automatizado

Para desarrollo, puedes usar el script integrado que ejecuta todos los servicios necesarios:

```bash
composer run dev
```

Esto iniciará automáticamente:
- Servidor Laravel (`php artisan serve`)
- Cola de trabajos (`php artisan queue:listen`)
- Logs en tiempo real (`php artisan pail`)
- Compilación de assets (`npm run dev`)

### Acceso al Panel de Administración

1. Navega a `http://localhost:8000/admin`
2. Inicia sesión con las credenciales del usuario administrador
3. Comienza a configurar tu negocio:
   - Añade colores y tallas
   - Registra productos
   - Configura categorías de gastos
   - Registra clientes

## 📱 Módulos del Sistema

### 🎨 Gestión de Atributos
- **Colores**: Paleta de colores disponibles
- **Tallas**: Rangos de tallas (XS, S, M, L, XL, XXL, etc.)
- **Variantes**: Combinaciones únicas de producto-color-talla

### 🛍️ Proceso de Venta
1. **Selección de Cliente**: Nuevo o existente
2. **Agregar Productos**: Selección de variantes y cantidades
3. **Aplicar Descuentos**: Descuentos fijos.
4. **Método de Pago**: Selección del método de pago
5. **Confirmación**: Generación automática del número de venta

### 💸 Control de Gastos
- **Categorías Personalizables**: Clasifica gastos por tipo
- **Registro Detallado**: Fecha, monto, descripción y categoría
- **Reportes**: Análisis de gastos por período y categoría

## 🧪 Testing

```bash
# Ejecutar todas las pruebas
composer run test

# Ejecutar pruebas específicas
php artisan test --filter=NombreDeLaPrueba
```

## 📈 Características Avanzadas

### Widgets del Dashboard
- **Resumen Financiero**: Ingresos vs gastos del período
- **Top Ventas**: Productos más vendidos
- **Análisis de Tallas**: Distribución de ventas por talla
- **Clientes Frecuentes**: Seguimiento de clientes VIP

### Funciones Inteligentes
- **Numeración Automática**: Sistema inteligente de numeración de ventas
- **Cálculos Automáticos**: Subtotales, descuentos y totales
- **Búsquedas Avanzadas**: Filtros múltiples en todas las secciones

## 🛡️ Seguridad

- Autenticación segura con Laravel
- Protección CSRF
- Validación de datos en servidor
- Sanitización de entradas

## 🤝 Contribución

¡Las contribuciones son bienvenidas! Por favor:

1. Haz fork del proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 👨‍💻 Autor

**Milton Alas Hernández**
- GitHub: [@miltonahdz](https://github.com/Milton-Alas)

## 🙏 Agradecimientos

- **Laravel** por el framework backend
- **Filament** por el panel de administración
- **Tailwind CSS** por el sistema de estilos
- La comunidad de desarrolladores PHP

---

<p align="center">
  Hecho con ❤️ para pequeños y medianos negocios de venta de camisas
</p>
