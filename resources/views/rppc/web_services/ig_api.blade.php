<x-base-layout :scrollspy="false">
   <x-slot:pageTitle>
   RPPC | Catastro
   </x-slot>
   <!-- BEGIN GLOBAL MANDATORY STYLES -->
   <x-slot:headerFiles>
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
   <link rel="stylesheet" href="{{asset('plugins/table/datatable/datatables.css')}}">
   @vite(['resources/scss/light/plugins/table/datatable/dt-global_style.scss'])
   @vite(['resources/scss/light/plugins/table/datatable/custom_dt_miscellaneous.scss'])
   @vite(['resources/scss/dark/plugins/table/datatable/dt-global_style.scss'])
   @vite(['resources/scss/dark/plugins/table/datatable/custom_dt_miscellaneous.scss'])
   <!--  END CUSTOM STYLE FILE  -->
   <style>
      .dt-table-hover td {
      cursor: pointer; /* Cambia el cursor a un puntero */
      }
   </style>
   
   </x-slot>
   <!-- END GLOBAL MANDATORY STYLES -->
   <style>
      .custom-dropdown .dropdown-menu.show {
      z-index: 1050;
      }
   </style>
   <div class="row layout-top-spacing">
      <h3>Sistema de Consultas de Claves Catastrales</h3>
   </div>

   <div class="simple-tab">
   <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
         <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Consultas</button>
      </li>
      <li class="nav-item" role="presentation">
         <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Registros</button>
      </li>
   </ul>


   <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
        
   <div class="row">
      <div id="flHorizontalForm" class="col-lg-12 layout-spacing">
         <div class="card shadow-sm">
            <div class="card-header ">
               <h4 class="mb-0">Por favor {{ auth()->user()->persona->nombre }} seleccione el municipio y la clave catastral a consultar</h4>
            </div>
            <div class="card-body">
               <div class="input-group mb-4">
                  <span class="input-group-text">Municipio</span>
                  <select class="form-control form-control-lg" id="municipio">
                  <option value="1">Cozumel</option>
                     <option value="2">Felipe Carrillo Puerto</option>
                     <option value="3">Isla Mujeres</option>
                     <option value="4">Othón P. Blanco</option>
                     <option value="5">Benito Juárez</option>
                     <option value="6">José María Morelos </option>
                     <option value="7">Lázaro Cárdenas </option>
                     <option value="8">Solidaridad </option>
                     <option value="9">Tulum </option>
                     <option value="10">Bacalar</option>
                     <option value="11">Puerto Morelos</option>
                  </select>
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">Cve. catastral</span>
                  <input type="text" class="form-control form-control-lg" placeholder="ej. 7505623XXXX" aria-label="Recipient's username" aria-describedby="button-addon2" id="cve_catastro">
               </div>
               <!-- Botón de búsqueda -->
               <div class="d-grid gap-2 col-6 mx-auto">
                  <!-- Default -->
                  <button class="btn btn-success mb-4" type="submit" id="aprobar-btn">
                     Buscar 
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                     </svg>
                  </button>
               </div>
               <h5 class="card-title mb-3">Detalles de la solicitud</h5>
               <hr class="mb-4">
               <div class="row g-3 mb-4">
                  <div class="col-md-4">
                     <div class="input-group">
                        <span class="input-group-text">Localidad</span>
                        <input type="text" class="form-control" id="localidad">
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="input-group">
                        <span class="input-group-text">Latitud</span>
                        <input type="text" class="form-control"  id="latitud">
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="input-group">
                        <span class="input-group-text">Longitud</span>
                        <input type="text" class="form-control" id="longitud">
                     </div>
                  </div>
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">CVE. Catastral</span>
                  <input type="text" class="form-control" aria-describedby="" id="catastro">
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">Nombre</span>
                  <input type="text" class="form-control" aria-describedby="" id="propietario">
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">Dirección</span>
                  <textarea class="form-control" id="direccion" rows="3"></textarea>
               </div>
               <h5>Datos RPPC</h5>
               <hr class="mb-4">
               <div class="row g-3 mb-4">
                  <div class="col-md-6">
                     <div class="input-group">
                        <span class="input-group-text">Tipo de persona</span>
                        <input type="text" class="form-control" id="persona">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="input-group">
                        <span class="input-group-text">Tipo de adjudicación</span>
                        <input type="text" class="form-control"  id="adj">
                     </div>
                  </div>
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">Folio real electrónico</span>
                  <input type="text" class="form-control" aria-describedby="" id="folio">
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">Titular</span>
                  <input type="text" class="form-control" aria-describedby="" id="titular">
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">CURP</span>
                  <input type="text" class="form-control" aria-describedby="" id="curp">
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">RFC</span>
                  <input type="text" class="form-control" aria-describedby="" id="rfc">
               </div>
               <div class="input-group mb-4">
                  <span class="input-group-text">Domicilio</span>
                  <textarea class="form-control" id="domi" rows="3"></textarea>
               </div>
               <hr>
               <div class="d-grid gap-2 col-6 mx-auto">
                  <!-- Default -->
                  <button class="btn btn-primary mb-4" type="submit" id="guardar-btn">
                     Guardar datos catastrales
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-database">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                        <path d="M3 5V19A9 3 0 0 0 21 19V5"/>
                        <path d="M3 12A9 3 0 0 0 21 12"/>
                     </svg>
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>
      </div>
      <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="statbox widget box box-shadow no-rounded-corners">
            <div class="widget-content widget-content-area">
                <table class="multi-table table dt-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Clave Catastral</th>
                            <th>Ubicación</th> <!-- Concatenar Municipio, Localidad, Latitud y Longitud -->
                            <th>Propietario/Titular</th> <!-- Concatenar Propietario y Titular -->
                            <th>Folio</th>
                            <th>Identificación</th> <!-- Concatenar CURP y RFC -->
                            <th>Tipo</th> <!-- Concatenar Tipo de Persona y Tipo de Adjudicación -->
                            <th>Domicilio</th>
                            <th>Fecha de Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($catastros as $dato)
                            <tr>
                                <td>{{ $dato->cve_catastro }}</td>
                                <td>
                                    {{ $dato->municipio }} - {{ $dato->localidad }}<br>
                                    Lat: {{ $dato->latitud }}, Long: {{ $dato->longitud }}
                                </td>
                                <td>
                                    {{ $dato->propietario }}<br>
                                    {{ $dato->titular }}
                                </td>
                                <td>{{ $dato->folio }}</td>
                                <td>
                                    CURP: {{ $dato->curp }}<br>
                                    RFC: {{ $dato->rfc }}
                                </td>
                                <td>
                                  PERSONA: {{ $dato->tipo_persona }}<br>
                                  ADJUDICACIÓN: {{ $dato->tipo_adjudicacion }}
                                </td>
                                <td>{{ $dato->domicilio }}</td>
                                <td>{{ $dato->fecha_creacion }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Clave Catastral</th>
                            <th>Ubicación</th>
                            <th>Propietario/Titular</th>
                            <th>Folio</th>
                            <th>Identificación</th>
                            <th>Tipo</th>
                            <th>Domicilio</th>
                            <th>Fecha de Creación</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
   </div>
</div>

   <!--  BEGIN CUSTOM SCRIPTS FILE  -->
   <x-slot:footerFiles>
   <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
   @vite(['resources/assets/js/custom.js'])
   <script src="{{asset('plugins/table/datatable/datatables.js')}}"></script>
   <script src="{{asset('plugins/table/datatable/button-ext/dataTables.buttons.min.js')}}"></script>
   <script src="{{asset('plugins/table/datatable/button-ext/jszip.min.js')}}"></script>
   <script src="{{asset('plugins/table/datatable/button-ext/buttons.html5.min.js')}}"></script>
   <script src="{{asset('plugins/table/datatable/button-ext/buttons.print.min.js')}}"></script>
   <script src="{{asset('plugins/table/datatable/custom_miscellaneous.js')}}"></script>
   <script>
  $(document).ready(function() {
      $('.btn-success').on('click', function() {
          const cve = $('#cve_catastro').val();
          const municipio = $('#municipio').val();

          function ejecutarRequest1() {
              return $.ajax({
                  url: '/consulta-catastro/' + municipio + '/' + cve,
                  method: 'GET'
              }).then(function(data1) {
                  $('#localidad').val(data1.nom_loc || 'Sin registros encontrados');
                  $('#catastro').val(data1.clave_catastral || 'Sin registros encontrados');
                  $('#propietario').val(data1.propietario || 'Sin registros encontrados');
                  $('#direccion').val(data1.direccion || 'Sin registros encontrados');
                  $('#latitud').val(data1.centroide_lat || 'Sin registros encontrados');
                  $('#longitud').val(data1.centroide_log || 'Sin registros encontrados');
              }).catch(function() {
                  $('#localidad').val('Sin registros encontrados');
                  $('#catastro').val('Sin registros encontrados');
                  $('#propietario').val('Sin registros encontrados');
                  $('#direccion').val('Sin registros encontrados');
                  $('#latitud').val('Sin registros encontrados');
                  $('#longitud').val('Sin registros encontrados');
              });
          }

          function ejecutarRequest2() {
              return $.ajax({
                  url: '/api/catastro/rppc/' + cve,
                  method: 'GET'
              }).then(function(data2) {
                  $('#folio').val(data2.crfre || 'Sin registros encontrados');
                  $('#titular').val(data2.titular || 'Sin registros encontrados');
                  $('#curp').val(data2.cupr || 'Sin registros encontrados');
                  $('#rfc').val(data2.rfc || 'Sin registros encontrados');
                  $('#persona').val(data2.tipo_persona || 'Sin registros encontrados');
                  $('#adj').val(data2.tipo_adjudicacion || 'Sin registros encontrados');
                  $('#domi').val(data2.domicilio || 'Sin registros encontrados');
              }).catch(function() {
                  $('#folio').val('Sin registros encontrados');
                  $('#titular').val('Sin registros encontrados');
                  $('#curp').val('Sin registros encontrados');
                  $('#rfc').val('Sin registros encontrados');
                  $('#persona').val('Sin registros encontrados');
                  $('#adj').val('Sin registros encontrados');
                  $('#domi').val('Sin registros encontrados');
              });
          }

          ejecutarRequest1(); 
          ejecutarRequest2(); 
      });
  });
</script>
   <script>
      $(document).ready(function() {
         $('#guardar-btn').on('click', function() {
            let boton = $(this);
      
            const datosGuardar = {
               _token: "{{ csrf_token() }}",
               cve_catastro: $('#cve_catastro').val(),
               municipio: $('#municipio').val(),
               localidad: $('#localidad').val(),
               latitud: $('#latitud').val(),
               longitud:  $('#longitud').val(),
               folio: $('#folio').val(),
               titular: $('#titular').val(),
               curp: $('#curp').val(),
               rfc: $('#rfc').val(),
               persona: $('#persona').val(),
               adj: $('#adj').val(),
               domi: $('#domi').val()
            };
      
            $.ajax({
               url: '/guardar/catastro',
               type: "POST",
               data: datosGuardar,
               success: function(response) {
                   Swal.fire({
                       icon: 'success',
                       title: 'Éxito',
                       text: response.message,
                   });
               },
               error: function(xhr) {
                   let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Ocurrió un error al guardar los datos.';
                   Swal.fire({
                       icon: 'error',
                       title: 'Error',
                       text: errorMessage,
                   });
               }
            });
         });
      });
   </script>
   </x-slot>
   <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>