<?php

public function replaceFiles(Request $request, $id)
{
    try {
        // Validar los datos recibidos
        $request->validate([
            "archivos" => "array",
            "archivos.rs" => "nullable|array",
            "archivos.rs.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
            "archivos.solicitud_rs" => "nullable|array",
            "archivos.solicitud_rs.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
            "archivos.oficio_rs" => "nullable|array",
            "archivos.oficio_rs.*" => "file|mimes:jpg,jpeg,png,pdf|max:2048",
        ]);

        $resolucion = Resolucion::findOrFail($id);
        $filesData = [];

        // Reemplazar archivos RS
        if (isset($request->archivos['rs'])) {
            $this->replaceFiles($request->archivos['rs'], $resolucion->resolucion_id, "RS", $filesData, ResolucionFile::class);
        }

        // Reemplazar archivos SOLICITUD_RS
        if (isset($request->archivos['solicitud_rs'])) {
            $this->replaceFiles($request->archivos['solicitud_rs'], $resolucion->resolucion_id, "SOLICITUD_RS", $filesData, SolicitudResolucion::class);
        }

        // Reemplazar archivos OFICIO_RS
        if (isset($request->archivos['oficio_rs'])) {
            $this->replaceFiles($request->archivos['oficio_rs'], $resolucion->resolucion_id, "OFICIO_RS", $filesData, OficioResolucion::class);
        }

        return response()->json([
            "success" => true,
            "message" => "Archivos reemplazados correctamente",
            "files" => $filesData,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            "success" => false,
            "error" => $e->getMessage(),
        ], 500);
    }
}

private function replaceFiles($files, $resolucionId, $prefix, &$filesData, $modelClass)
{
    // Buscar y eliminar archivos existentes
    $existingFiles = $modelClass::where('resolucion_id', $resolucionId)->get();
    foreach ($existingFiles as $oldFile) {
        // Eliminar el archivo del almacenamiento
        Storage::disk("ftp")->delete($oldFile->file_path);
        // Eliminar el registro de la base de datos
        $oldFile->delete();
    }

    // Procesar y almacenar nuevos archivos
    foreach ($files as $file) {
        if ($file && $file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $uniqueName = $prefix . "-" . time() . "-" . pathinfo($originalName, PATHINFO_FILENAME) . "." . $file->getClientOriginalExtension();
            $path = $file->storeAs("archivos", $uniqueName, "ftp");

            // Guardar el archivo en la base de datos
            $modelClass::create([
                "resolucion_id" => $resolucionId,
                "file_path" => $path,
            ]);

            // Agregar datos al array de respuesta
            $filesData[] = [
                "type" => $prefix,
                "path" => $path,
                "original_name" => $originalName,
            ];
        }
    }
}
