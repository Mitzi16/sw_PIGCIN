<?php

namespace App\Http\Controllers\Traits;

use App\Models\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait SignatureTrait
{
    /**
     * Lógica principal para generar la firma/sello digital.
     * Es usada por ConstanciaController y VerificationController
     */
    private function generateSignature(Article $article): string
    {
        $varCER = 'VIICyT/VIICyT.cer';
        $varKEY = 'VIICyT/VIICyT.key';
        $keyPasswd = 'viicyt2024';

        // obtener los archivos
        $dataCER = Storage::disk('public')->get($varCER);
        $dataKEY = Storage::disk('public')->get($varKEY);

        if (!$dataCER || !$dataKEY) {
            throw new \Exception('No se encontraron los archivos .cer o .key en public storage.');
        }

        // Obtener la clave privada
        $resKEY = openssl_pkey_get_private($dataKEY, $keyPasswd);
        if ($resKEY === false) {
            Log::error("openssl_pkey_get_private falló. ¿Contraseña incorrecta o clave corrupta?");
            throw new \Exception('No se pudo obtener la clave privada. Verifique la contraseña.');
        }

        // Preparar el string para la firma
        $string = '||' . $article->id . '||' . $article->title . '||';
        $stringMD5 = md5($string);

        // Encriptar el string MD5
        $success = openssl_private_encrypt($stringMD5, $encrypted_string, $resKEY);
        
        // Liberar la clave de memoria
        openssl_pkey_free($resKEY);

        if (!$success) {
            throw new \Exception('Fallo al encriptar (firmar) el string: ' . openssl_error_string());
        }

        // Codificar en Base64
        return base64_encode($encrypted_string);
    }
}