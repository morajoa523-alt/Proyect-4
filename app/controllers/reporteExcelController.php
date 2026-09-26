<?php 


class reporteExcelController extends Controller
{
    private $dao;

    public function __construct()
    {
        // Cargamos el DAO correcto
        $this->dao = new DaoDatosBD();
    }

    /**
     * Genera un reporte Excel anual
     * ejemplo: /reporteExcel/generar?anio=2024
     */
    public function generar()
    {
        $anio = $_GET['anio'] ?? date('Y');

        // ===== DATOS DESDE EL DAO =====
        $comprasPorMes      = $this->dao->ComprasXmes($anio);
        $productosAnual     = $this->dao->ComprasXproductosAnual($anio);
        $ventasPorSexo      = $this->dao->comprasXsegmentosMFanio($anio);
        $comprasPorCiudad   = $this->dao->comprasXciudadAnual($anio);
        $totalVentas        = $this->dao->TotalVentas($anio);
        $totalProductos     = $this->dao->TotalProductosVendidos($anio);

        // ===== HEADERS EXCEL =====
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=reporte_$anio.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<h2>Reporte Anual $anio</h2>";

        echo "<p><strong>Total Ventas:</strong> $totalVentas</p>";
        echo "<p><strong>Total Productos Vendidos:</strong> $totalProductos</p>";

        // ===== COMPRAS POR MES =====
        echo "<h3>Compras por Mes</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Mes</th>
                    <th>Total Ventas</th>
                </tr>";

        foreach ($comprasPorMes as $row) {
            if (isset($row['mes'])) {
                echo "<tr>
                        <td>{$row['mes']}</td>
                        <td>{$row['totalVentas']}</td>
                      </tr>";
            }
        }
        echo "</table>";

        // ===== PRODUCTOS MÁS VENDIDOS =====
        echo "<h3>Productos más vendidos</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Producto</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Precio</th>
                    <th>Total Vendido</th>
                </tr>";

        foreach ($productosAnual as $row) {
            if (isset($row['producto'])) {
                echo "<tr>
                        <td>{$row['producto']}</td>
                        <td>{$row['marca']}</td>
                        <td>{$row['modelo']}</td>
                        <td>{$row['precio']}</td>
                        <td>{$row['total_vendido']}</td>
                      </tr>";
            }
        }
        echo "</table>";

        // ===== VENTAS POR SEXO =====
        echo "<h3>Ventas por Segmento (Sexo)</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Sexo</th>
                    <th>Cantidad</th>
                </tr>";

        foreach ($ventasPorSexo as $row) {
            if (isset($row['sexo'])) {
                echo "<tr>
                        <td>{$row['sexo']}</td>
                        <td>{$row['cantidad']}</td>
                      </tr>";
            }
        }
        echo "</table>";

        // ===== COMPRAS POR CIUDAD =====
        echo "<h3>Compras por Ciudad</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Ciudad</th>
                    <th>Provincia</th>
                    <th>Total Compras</th>
                    <th>Total Clientes</th>
                </tr>";

        foreach ($comprasPorCiudad as $row) {
            if (isset($row['ciudad'])) {
                echo "<tr>
                        <td>{$row['ciudad']}</td>
                        <td>{$row['provincia']}</td>
                        <td>{$row['totalCompras']}</td>
                        <td>{$row['totalClientes']}</td>
                      </tr>";
            }
        }
        echo "</table>";
    }
}



?>
