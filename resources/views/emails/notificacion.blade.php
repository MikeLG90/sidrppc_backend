<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        burgundy: {
                            DEFAULT: '#800020',
                            dark: '#5c0018'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="m-0 p-0 font-sans bg-gray-50 text-gray-800">
    <div class="max-w-full py-12 px-3 bg-gray-50">
        <!-- Container -->
        <div class="max-w-[600px] mx-auto rounded-2xl overflow-hidden shadow-lg">
            <!-- Header -->
            <div class="bg-gradient-to-r from-burgundy to-burgundy-dark p-8 text-center">
                <div class="inline-block bg-white/10 py-3 px-6 rounded-lg">
                    <h1 class="m-0 text-white text-2xl font-bold tracking-tight">{{ $clase }}</h1>
                </div>
            </div>
            
            <!-- Content -->
            <div class="bg-white p-8">
                <div class="border-l-4 border-burgundy pl-4 mb-6">
                    <p class="m-0 text-gray-600 text-base leading-relaxed font-medium">{{ $mensaje }}</p>
                </div>
            </div>
            <div class="bg-white px-8 pb-8">
                <div class="border-l-4 border-burgundy pl-4 mb-6">
                    <a href="{{ $link }}" class="inline-block bg-burgundy hover:bg-burgundy-dark text-white font-medium py-2 px-6 rounded-lg transition-colors duration-200">Click aquí para ir al sistema y ver la resolución</a>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="m-0 text-gray-500 text-sm text-center">Este es un mensaje oficial del sistema.</p>
                </div>
            </div>
            <!-- Footer -->
            <div class="bg-burgundy-dark p-6 text-center">
                <p class="m-0 text-white text-sm font-medium">Sistema Integral de Datos del Registro Público de la Propiedad y de Comercio</p>
                <div class="w-12 h-0.5 bg-white/30 mx-auto my-4"></div>
                <p class="m-0 text-white/80 text-xs">© 2025 Todos los derechos reservados.</p>
            </div>
        </div>
        
        <!-- Disclaimer -->
        <div class="max-w-[580px] mx-auto mt-6 text-center">
            <p class="m-0 text-gray-400 text-xs">Este correo es confidencial y está destinado únicamente para el destinatario indicado.</p>
        </div>
    </div>
</body>
</html>