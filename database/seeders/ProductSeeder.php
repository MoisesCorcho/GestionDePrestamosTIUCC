<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $productsData = [
            'AMPLIFICACION' => [
                'marca' => 'Yamaha',
                'modelo' => 'RX-V685',
                'descripcion' => 'Amplificador de audio de 7.2 canales con Dolby Atmos y DTS:X.',
            ],
            'APUNTADOR' => [
                'marca' => 'Logitech',
                'modelo' => 'Presenter R400',
                'descripcion' => 'Presentador inalámbrico con puntero láser rojo y funciones de control de diapositivas.',
            ],
            'CABLE 2X1 RCA' => [
                'marca' => 'Monster Cable',
                'modelo' => 'RCA 2.0',
                'descripcion' => 'Cable RCA estéreo de alta calidad con conectores chapados en oro.',
            ],
            'CABLE HDMI' => [
                'marca' => 'Belkin',
                'modelo' => 'HDMI 2.1',
                'descripcion' => 'Cable HDMI de alta velocidad para 4K y 8K, compatible con HDR.',
            ],
            'CONSOLA MIC YAMAHA' => [
                'marca' => 'Yamaha',
                'modelo' => 'MG10XU',
                'descripcion' => 'Consola de mezcla analógica de 10 canales con efectos digitales.',
            ],
            'CONTROL PROYECTOR' => [
                'marca' => 'Universal',
                'modelo' => 'RC-100',
                'descripcion' => 'Control remoto universal compatible con múltiples marcas de proyectores.',
            ],
            'CONTROL TV' => [
                'marca' => 'LG',
                'modelo' => 'AN-MR600',
                'descripcion' => 'Control remoto original para televisores LG.',
            ],
            'MICRO BOYA' => [
                'marca' => 'Rode',
                'modelo' => 'VideoMic Me-NTG',
                'descripcion' => 'Micrófono de solapa omnidireccional para cámaras DSLR y videocámaras.',
            ],
            'MICRO INALAMBRICO' => [
                'marca' => 'Shure',
                'modelo' => 'SM58-SE',
                'descripcion' => 'Sistema de micrófono inalámbrico dinámico con receptor de un solo canal.',
            ],
            'MICRO JABRA' => [
                'marca' => 'Jabra',
                'modelo' => 'Elite 75t',
                'descripcion' => 'Auriculares Bluetooth con micrófono integrado para llamadas y música.'
            ],
            'MICRO SOLAPA' => [
                'marca' => 'Rode',
                'modelo' => 'VideoMic Me-NTG',
                'descripcion' => 'Micrófono de solapa omnidireccional para cámaras DSLR y videocámaras, ideal para grabaciones de alta calidad en movimiento.',
            ],
            'PANTALLA INTERACTIVA' => [
                'marca' => 'Promethean',
                'modelo' => 'ActivPanel',
                'descripcion' => 'Pantalla interactiva táctil multi-usuario para educación y colaboración.',
            ],
            'PARLANTE' => [
                'marca' => 'Bose',
                'modelo' => 'SoundLink Revolve+',
                'descripcion' => 'Parlante portátil Bluetooth con sonido envolvente de 360 grados.',
            ],
            'PATCH CORD (RED)' => [
                'marca' => 'Monoprice',
                'modelo' => 'Cat6',
                'descripcion' => 'Cable de red Ethernet Cat6 para conexiones de alta velocidad.',
            ],
            'PORTATIL HP' => [
                'marca' => 'HP',
                'modelo' => 'Spectre x360',
                'descripcion' => 'Portátil convertible 2 en 1 con pantalla táctil y procesador Intel Core i7.',
            ],
            'PORTATIL LENOVO' => [
                'marca' => 'Lenovo',
                'modelo' => 'ThinkPad X1 Carbon',
                'descripcion' => 'Portátil ultradelgado y ligero, ideal para profesionales.',
            ],
            'PROYECTOR EPSON' => [
                'marca' => 'Epson',
                'modelo' => 'EH-TW7100',
                'descripcion' => 'Proyector láser 4K con alta luminosidad y amplio rango de colores.',
            ],
            'TELEVISOR' => [
                'marca' => 'Samsung',
                'modelo' => 'Neo QLED QN90A',
                'descripcion' => 'Televisor QLED 4K con tecnología Mini LED para un contraste excepcional.',
            ],
            'TELON DE BASE' => [
                'marca' => 'Elite Screens',
                'modelo' => '雅緻系列',
                'descripcion' => 'Telón de proyección motorizado con relación de aspecto 16:9.',
            ],
            'TABLET' => [
                'marca' => 'Apple',
                'modelo' => 'iPad Pro',
                'descripcion' => 'Tablet de alta gama con pantalla Liquid Retina y chip M1.',
            ],
        ];

        foreach ($productsData as $productName => $productInfo) {
            Product::create([
                'nombre' => $productName,
                'marca' => $productInfo['marca'],
                'modelo' => $productInfo['modelo'],
                'descripcion' => $productInfo['descripcion'],
            ]);
        }

        // Product Units (20 units per product, with random data)
        foreach (Product::all() as $product) {
            for ($i = 1; $i <= 20; $i++) {
                $productUnit = ProductUnit::create([
                    'product_id' => $product->id,
                    'codigo_inventario' => 'INV-' . $product->id . '-' . $i,
                    'serie' => 'SER-' . $product->id . '-' . $i,
                    'estado' => ['dañado', 'disponible'][array_rand(['dañado', 'disponible'])],
                    'descripcion_lugar' => 'Ubicación ' . rand(1, 10),
                    'funcionario_responsable' => 'Funcionario ' . rand(1, 10),
                    'fecha_asignacion' => now()->subDays(rand(1, 30)),
                ]);

                if ($productUnit->estado == 'disponible') {
                    $product->cantidad += 1;
                    $product->save();
                }
            }
        }
    }
}
