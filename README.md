<p align="center">
  <img src="public/images/logo-ecolacteos.png" alt="Ecolácteos Huata" width="120">
</p>

<h1 align="center">Ecolácteos Huata</h1>

<p align="center">
  Sistema web de gestión y acopio de leche, producción de lácteos y venta en línea.
</p>

---

## Contenido

- [Descripción](#descripción)
- [Funcionalidades por rol](#funcionalidades-por-rol)
- [Tecnologías](#tecnologías)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Cuentas de prueba](#cuentas-de-prueba)
- [Configuración del sistema](#configuración-del-sistema)
- [Reglas de negocio](#reglas-de-negocio)
- [Pruebas](#pruebas)
- [Estructura del proyecto](#estructura-del-proyecto)

## Descripción

Ecolácteos Huata cubre todo el recorrido de la leche:

1. **Acopio:** los acopiadores registran los litros recolectados a cada productor.
2. **Calidad:** el laboratorio analiza cada entrega (con lectura OCR opcional del reporte).
3. **Producción:** la planta elabora productos según recetas y descuenta leche e insumos automáticamente.
4. **Pagos:** los productores cobran por litro y los acopiadores reciben un sueldo mensual.
5. **Venta:** los clientes compran en la tienda web, con o sin cuenta.

Todo se administra desde un panel por roles, con una sola página de inicio de sesión.

## Funcionalidades por rol

| Rol | Qué puede hacer |
|---|---|
| **Administrador** | Gestión completa: usuarios, acopiadores (asignación de comunidad y productores), productores, clientes, productos, insumos, pagos (liquidaciones y planilla), calidad, sanciones, quejas, avisos, precio de la leche y configuración del sistema. Puede **ver el sistema como otro usuario** sin conocer su contraseña. |
| **Gerente** | Consulta de productores, acopio, calidad, pagos, producción, insumos, ventas y clientes. No puede modificar la configuración ni realizar pagos. |
| **Acopiador** | Registra acopio (solo productor, litros y observaciones; fecha, vehículo y precio son automáticos), ve sus productores asignados, su jornada y sus **boletas de sueldo mensual**. |
| **Control de calidad** | Registra análisis de laboratorio (con OCR opcional) y consulta el historial. |
| **Trabajador de planta** | Crea **recetas** (ingredientes por unidad de producto), registra **producción** eligiendo solo producto y cantidad, consulta insumos y la leche recibida por día, y gestiona ventas y stock. |
| **Productor** | Consulta sus entregas, calidad, **liquidaciones con comprobante descargable**, quejas y notificaciones. |
| **Cliente (tienda web)** | Catálogo, carrito, pedido con o sin cuenta e historial de pedidos. |

### Módulos destacados

- **Centro de pagos:** liquidaciones por litro para productores (con bonos y descuentos) y planilla mensual de sueldo fijo para acopiadores. Todos los comprobantes se pueden descargar en PDF desde el navegador.
- **Producción automática:** a partir de la receta, el sistema calcula la leche y los insumos necesarios, toma la leche de las entregas más antiguas disponibles y descuenta el stock.
- **Configuración del sistema:** los datos de la empresa (dirección, teléfono, correo, redes) se reflejan en la web pública y en los comprobantes. Las reglas de acopio, pagos y calidad también se configuran aquí.
- **Clientes:** listado de clientes registrados e invitados, con sus pedidos y el total comprado.

## Tecnologías

- **Backend:** PHP 8.3 y Laravel 13
- **Base de datos:** MySQL 8
- **Frontend:** Blade, CSS propio y Tailwind CSS 4, compilados con Vite
- **Gráficos:** Chart.js
- **OCR (opcional):** Tesseract mediante `thiagoalessio/tesseract_ocr`
- **Pruebas:** PHPUnit

## Requisitos

- PHP 8.3 o superior, con las extensiones `pdo_mysql`, `mbstring` y `openssl`
- Composer 2
- Node.js 20 o superior y npm
- MySQL 8 (por ejemplo con Laragon o XAMPP)
- *(Opcional)* [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) instalado en el sistema para leer automáticamente los reportes de calidad

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/Lincol15/ecolacteos.git
cd ecolacteos

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar el entorno
cp .env.example .env
php artisan key:generate
```

Edita `.env` con los datos de tu base de datos (créala antes en MySQL):

```env
APP_NAME="Ecolácteos Huata"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=limon
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 4. Crear las tablas y cargar los datos de ejemplo
php artisan migrate --seed

# 5. Compilar los estilos y scripts
npm run build

# 6. Levantar el servidor
php artisan serve
```

Abre **http://localhost:8000**. Para desarrollo con recarga automática de estilos, usa `npm run dev` en otra terminal.

> Si las páginas del panel se ven sin estilos, borra el archivo `public/hot` (lo deja `npm run dev` si se cerró de forma inesperada) y ejecuta `npm run build`.

## Cuentas de prueba

Las crea `php artisan migrate --seed`. Se puede iniciar sesión con el **correo o el DNI** desde el botón **Iniciar Sesión**.

| Rol | Correo | DNI | Contraseña |
|---|---|---|---|
| Administrador | admin@ecolacteos.com | 00000001 | `admin123` |
| Gerente | gerente@ecolacteos.com | 00000002 | `gerente123` |
| Acopiador | acopiador@ecolacteos.com | 00000003 | `acopiador123` |
| Control de calidad | calidad@ecolacteos.com | 00000004 | `calidad123` |
| Trabajador de planta | planta@ecolacteos.com | 00000005 | `planta123` |
| Productores (1 al 8) | productor1@ecolacteos.com … productor8@ecolacteos.com | 40123001 … 40123008 | `productor123` |

Los clientes de la tienda crean su cuenta desde **Iniciar Sesión → Crear cuenta**.

> ⚠️ Son contraseñas de ejemplo. **Cámbialas antes de publicar el sistema.**

## Configuración del sistema

Desde **Admin → Configuración**, sin tocar código:

| Sección | Ajustes | Dónde se usa |
|---|---|---|
| Empresa y contacto | Nombre, RUC, dirección, teléfono, WhatsApp, correo, horario, Facebook e Instagram | Web pública (pie de página, Contacto, Inicio) y comprobantes |
| Acopio y pagos | Precio por litro, máximo de litros por entrega, día de pago, mínimos para los bonos por volumen y calidad | Registro de acopio y liquidaciones |
| Control de calidad | Porcentaje máximo de agua añadida | Análisis de laboratorio |

El sueldo mensual de cada acopiador se define en **Admin → Pagos → Acopiadores**.

## Reglas de negocio

- **Precio de la leche:** lo fija solo el administrador. El acopiador no puede modificarlo.
- **Asignación de productores:** cada acopiador atiende los productores que el admin le marca. Si solo se elige una comunidad, se asignan todos sus productores activos.
- **Liquidación del productor** = litros × precio + bono por calidad (5 %) + bono por volumen (3 %) − fondo solidario (1 %) − leche llevada directo a planta.
- **Acopiador:** sueldo mensual fijo que no depende de los litros. La planilla se genera una vez por mes y no se duplica.
- **Producción:** solo se pueden producir productos con receta. La leche se toma de las entregas más antiguas disponibles (cada entrega se usa completa) y los insumos se descuentan según la receta. Si falta leche o algún insumo, no se crea el lote.
- **Insumos:** la leche no se puede eliminar. Un insumo usado en recetas debe quitarse primero de ellas, y si tiene movimientos se oculta pero conserva su historial.

## Pruebas

```bash
php artisan test
```

`phpunit.xml` usa SQLite en memoria, lo que requiere la extensión `pdo_sqlite`. Si no la tienes, ejecuta las pruebas contra una base MySQL **separada**, porque las pruebas borran los datos de la base que usan:

```bash
# Crear una base solo para pruebas (una vez)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS limon_test"

# Linux / macOS
DB_CONNECTION=mysql DB_DATABASE=limon_test php artisan test

# Windows (PowerShell)
$env:DB_CONNECTION='mysql'; $env:DB_DATABASE='limon_test'; php artisan test
```

> Nunca ejecutes las pruebas contra la base de datos real del proyecto.

## Estructura del proyecto

```
app/
├── Http/Controllers/     # Un controlador por rol: Admin, Collector, Plant, Producer, Quality,
│                         # más Home (web pública y login), Cart, CustomerAuth, Impersonation
├── Http/Requests/        # Validaciones de formularios
├── Models/               # Modelos Eloquent (MilkDelivery, Payment, CollectorPayment, Recipe, PlantConfig…)
├── Observers/            # Cálculos automáticos al guardar entregas, pagos y análisis
└── Services/             # Lógica de negocio: pagos, planilla, producción, calidad, OCR
database/
├── migrations/
├── factories/
└── seeders/DatabaseSeeder.php   # Usuarios, productos, recetas y datos de ejemplo
resources/
├── css/app.css           # Estilos del panel
├── js/app.js             # Menú lateral y ventana de cierre de sesión
└── views/
    ├── admin/ collector/ plant/ producer/ quality/   # Vistas por rol
    ├── public/           # Web pública y tienda
    ├── payments/         # Comprobantes imprimibles
    ├── components/       # Íconos, estado de pago, leche por día
    └── layouts/          # Plantilla del panel y menú lateral
routes/web.php            # Rutas agrupadas por rol
tests/Feature/            # Pruebas de funcionalidades
```

---

<p align="center">Desarrollado por <strong>4bytes</strong> para Ecolácteos Huata.</p>
