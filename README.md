# DASHBOARDS — Sistema de visualización de patrones de compra (Almacén de Calzado)

Aplicación web desarrollada en **PHP puro** con arquitectura **MVC**, orientada al análisis y visualización de patrones de compra de un almacén de calzado. Transforma las ventas registradas en la base de datos en indicadores, porcentajes y gráficos interactivos que apoyan la toma de decisiones comerciales.

## Funcionalidades principales

- **Panel general (inicio):** resumen anual de compras por producto, por segmento de cliente (sexo), por ciudad y por mes, con selector de año.
- **Preferencias por mes:** desglose de ventas de un mes específico por producto, segmento, sesión/horario y ciudad, con detalle diario al hacer clic en un día.
- **Preferencias por día:** consulta puntual de las ventas de un día concreto (con o sin filtro de segmento).
- **Análisis por segmento (Femenino / Masculino):** ventas mensuales, productos más vendidos, tallas, sesiones y distribución por ciudad para cada segmento.
- **Reportes:** exportación de reportes anuales a Excel (ventas por mes, productos más vendidos, ventas por sexo, compras por ciudad).
- **Gestión de accesos a reportes:** administración de usuarios habilitados para ver reportes y su tipo de acceso.
- **Gestión de usuarios:** alta, edición y eliminación de usuarios, con contraseñas cifradas (bcrypt) y roles diferenciados (Admin, Analista, Usuario avanzado, Usuario reporte), reflejados en sidebars distintos según el tipo de usuario.
- **Autenticación:** login, cierre de sesión y panel reducido para usuarios sin privilegios de administrador.

## Arquitectura técnica

- **Patrón:** MVC hecho a mano (sin framework), con `Core`/`Router` propios que resuelven rutas desde `?url=` vía `.htaccess` y `mod_rewrite`.
- **Backend:** PHP + PDO/MySQL, conexión centralizada en `app/config/Database.php` (patrón singleton).
- **Cálculo de indicadores:** helper `app/helpers/porcentajes.php`, que convierte los totales devueltos por los DAOs en porcentajes por producto, segmento, sesión, ciudad, mes y talla.
- **Persistencia de filtros:** el año y las preferencias de mes/gráfico seleccionados se guardan en archivos JSON (`app/helpers/datosJson/`) para mantener el estado entre vistas.
- **Base de datos:** MySQL (`BD Calzado.sql`), con tablas para usuarios y tipos de usuario, categorías, tallas, productos, ventas y detalle de ventas, además de accesos a reportes.
- **Frontend:** HTML/CSS (Bootstrap) y JavaScript por vista, con gráficos renderizados a partir de los datos que entregan los controladores (uno por tipo de dashboard: productos, segmento, ciudad, días, meses).

## Estructura del proyecto

```
app/
 ├─ controllers/   # interfacesController (dashboard general), preferenciasController (mes/día/segmento),
 │                  # totalesPorcentajesController (drill-down de gráficos), reporteExcelController, userController
 ├─ models/         # DAOs: DaoDatosBD, DaoDatosXdia, DaoDatosXsegm, DaoUsuario
 ├─ views/          # Vistas por rol (Admin/Analista/UserAvanzado/UserReporte) y por tipo de gráfico
 ├─ lib/            # Core y Router del framework casero
 ├─ helpers/        # Cálculo de porcentajes, sesiones, cookies, JSON de estado de filtros
 └─ config/         # Configuración de la app y conexión a BD
public/
 ├─ index.php       # Punto de entrada
 ├─ css/ js/ img/    # Recursos estáticos
BD Calzado.sql      # Script de creación y datos de la base de datos
```

## Instalación local

1. Copiar el proyecto en el servidor (ej. `htdocs/DASHBOARDS`).
2. Crear la base de datos e importar `BD Calzado.sql`.
3. Ajustar credenciales de conexión en `app/config/Database.php` si es necesario.
4. Ajustar la URL base en `app/config/config.php` (`URL`) según el entorno.
5. Servir el proyecto con Apache (mod_rewrite habilitado) apuntando a la carpeta `public/`.


