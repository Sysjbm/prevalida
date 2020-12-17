<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Reps;

class ImportController extends Controller
{
    public function __construct()
    {
    }
    public function get_table($mes = null, $year = null){
        $tables = \DB::connection('bdua')->select('SHOW TABLES');
        $tables = array_map('current',$tables);
        $table = array();
        $i = 0;
        foreach ($tables as $key => $value) {
            $explode = explode('_',$value);
            $explode_mes = str_split($explode[3], 4);
            array_push($explode_mes, $value);
            if($mes == $explode_mes[1] and $year == $explode_mes[0]){
                $table[$i] = $explode_mes[2];
                $i++;
            }
        }
        return $table;
    }
    function stdToArray($obj){
        $reaged = (array)$obj;
        foreach($reaged as $key => &$field){
            if(is_object($field))$field = $this->stdToArray($field);
        }
        return $reaged;
    }
    public function index(Request $request)
    {
        return view('home');
    }
    public function uploadfile(Request $request){
        return view('validador');
    }
    public function uploadFilePost(Request $request){
        $file = $request->file('filename');
        $mes = $request->input('mes');
        $year = $request->input('year');
        $fecha = date_create();
        if($file->getClientOriginalExtension() == "xlsx"){
            $destinationPath = 'storage/files';
            $nameFile = date_timestamp_get($fecha).".xlsx";
            $file->move($destinationPath,$nameFile);
            if($mes < 10){$mes = "0".$mes;}
            //$mes = '09'; $year = '2019';
            $log = $this->validarFile($nameFile, array('table' => $this->get_table($mes,$year)));
            if($log == ""){
                $log = "NO SE ENCONTRARON ERRORES. LA VALIDACIÓN FUE ÉXITOSA!";
            }
            return view('alertas',['log' => $log]);
        }else{
            return back()->with('error','El archivo que intentas subir no es .xlsx');
        }
    }
    public function validarFile($file,$table){
        $log = "";
        $read = Excel::load('public/storage/files/'.$file)->get();
        foreach ($read as $key => $value) {
            $key = $key+2;
            if($value->numero_identificacion != ""){
                if($table['table']){
                    foreach ($table as $sql_value) {
                        $sql = "SELECT * FROM ".$sql_value[1]." WHERE doc_afil = ".$value->numero_identificacion;
                        $result = \DB::connection('bdua')->select($sql);
                        $result = $this->stdToArray($result);
                    }
                    if($result){
                        if($result[0]['estado'] != "AC"){
                            $log.= "<b>Error E".$key."</b> Usuario retirado. <br>";
                        }
                        if($result[0]['apellido_1'] != $value->primer_apellido || preg_match("/[][{}()*+?.\\^$|]/", $value->primer_apellido)){
                            $log.= "<b>Error F".$key."</b> Error en primer apellido. <br>";
                        }
                        if($result[0]['apellido_2'] != $value->segundo_apellido || preg_match("/[][{}()*+?.\\^$|]/", $value->primer_apellido)){
                            $log.= "<b>Error G".$key."</b> Error en segundo apellido. <br>";
                        }
                        if($result[0]['nombre_1'] != $value->primer_nombre || preg_match("/[][{}()*+?.\\^$|]/", $value->primer_apellido)){
                            $log.= "<b>Error H".$key."</b> Error en primer nombre. <br>";
                        }
                        if($result[0]['nombre_2'] != $value->segundo_nombre || preg_match("/[][{}()*+?.\\^$|]/", $value->primer_apellido)){
                            $log.= "<b>Error I".$key."</b> Error en segundo nombre. <br>";
                        }
                        if($result[0]['sex_afil'] != $value->sexo){
                            $log.= "<b>Error K".$key."</b> Error en sexo. <br>";
                        }
                    }else{
                        $log.= "<b>Error E".$key."</b> Usuario no encontrado. <br>";
                    }
                }else{
                    $log.= "<b style='color:red'>ADVERTENCIA!</b><b> NO SE ENCONTRARON REGISTROS BDUA PARA ".$value->numero_identificacion."</b><br>";
                }
            }
            $log.= $value->tipo_de_registro ? "" : "<b>Error A".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consecutivo_registro ? "" : "<b>Error B".$key."</b> No debe estar vacia. <br>";
            $log.= $value->codigo_habilitacion_ips ? "" : "<b>Error C".$key."</b> No debe estar vacia. <br>";
            if($value->codigo_habilitacion_ips != ""){
                if(!Reps::where('cod_habilitacion',$value->codigo_habilitacion_ips)->first()){
                    $log.= "<b>Error C".$key."</b> Codigo habilitacion no existe. <br>";
                }
            }
            $log.= $value->tipo_de_identificacion ? "" : "<b>Error D".$key."</b> No debe estar vacia. <br>";
            $log.= $value->numero_identificacion ? "" : "<b>Error E".$key."</b> No debe estar vacia. <br>";
            $log.= $value->primer_apellido ? "" : "<b>Error F".$key."</b> No debe estar vacia. <br>";
            $log.= $value->segundo_apellido ? "" : "<b>Error G".$key."</b> No debe estar vacia. <br>";
            $log.= $value->primer_nombre ? "" : "<b>Error H".$key."</b> No debe estar vacia. <br>";
            $log.= $value->segundo_nombre ? "" : "<b>Error I".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_de_nacimiento ? "" : "<b>Error J".$key."</b> No debe estar vacia. <br>";
            $log.= $value->sexo ? "" : "<b>Error K".$key."</b> No debe estar vacia. <br>";
            if($value->codigo_pertenencia_etnica < 0){
                $log.= "<b>Error L".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->coidigo_ocupacion ? "" : "<b>Error M".$key."</b> No debe estar vacia. <br>";
            $log.= $value->codigo_nivel_educativo ? "" : "<b>Error N".$key."</b> No debe estar vacia. <br>";
            if($value->gestacion < 0){
                $log.= "<b>Error O".$key."</b> No debe estar vacia. <br>";
            }
            if($value->sifilis_gestacional_o_congenita < 0){
                $log.= "<b>Error P".$key."</b> No debe estar vacia. <br>";
            }
            if($value->hipertension_inducida_por_la_gestacion < 0){
                $log.= "<b>Error Q".$key."</b> No debe estar vacia. <br>";
            }
            if($value->hipotiroidismo_congenito < 0){
                $log.= "<b>Error R".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->sintomatico_respiratorio ? "" : "<b>Error S".$key."</b> No debe estar vacia. <br>";
            if($value->tuberculosis_multidrogoresistente < 0){
                $log.= "<b>Error T".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->lepra ? "" : "<b>Error U".$key."</b> No debe estar vacia. <br>";
            $log.= $value->obesidad_o_desnutricion_proteico_calorica ? "" : "<b>Error V".$key."</b> No debe estar vacia. <br>";
            if($value->victima_de_maltrato < 0){
                $log.= "<b>Error W".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->victima_de_violencia_sexual ? "" : "<b>Error X".$key."</b> No debe estar vacia. <br>";
            $log.= $value->infecciones_de_trasmision_sexual ? "" : "<b>Error Y".$key."</b> No debe estar vacia. <br>";
            $log.= $value->enfermedad_mental ? "" : "<b>Error Z".$key."</b> No debe estar vacia. <br>";
            if($value->cancer_de_cervix < 0){
                $log.= "<b>Error AA".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->cancer_de_seno ? "" : "<b>Error AB".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fluorosis_dental ? "" : "<b>Error AC".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_del_peso ? "" : "<b>Error AD".$key."</b> No debe estar vacia. <br>";
            $log.= $value->peso_en_kilogramos ? "" : "<b>Error AE".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_de_la_talla ? "" : "<b>Error AF".$key."</b> No debe estar vacia. <br>";
            $log.= $value->talla_en_centimetros ? "" : "<b>Error AG".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_probable_de_parto ? "" : "<b>Error AH".$key."</b> No debe estar vacia. <br>";
            if($value->edad_gestacional_al_nacer < 0){
                $log.= "<b>Error AI".$key."</b> No debe estar vacia. <br>";
            }
            if($value->bcg < 0){
                $log.= "<b>Error AJ".$key."</b> No debe estar vacia. <br>";
            }
            if($value->hepatitis_b_menores_de_1_ano < 0){
                $log.= "<b>Error AK".$key."</b> No debe estar vacia. <br>";
            }
            if($value->pentavalente < 0){
                $log.=  "<b>Error AL".$key."</b> No debe estar vacia. <br>";
            }
            if($value->polio < 0){
                $log.= "<b>Error AM".$key."</b> No debe estar vacia. <br>";
            }
            if($value->dpt_menores_de_5_anos < 0){
                $log.= "<b>Error AN".$key."</b> No debe estar vacia. <br>";
            }
            if($value->rotavirus < 0){
                $log.= "<b>Error AO".$key."</b> No debe estar vacia. <br>";
            }
            if($value->neumococo < 0){
                $log.= "<b>Error AP".$key."</b> No debe estar vacia. <br>";
            }
            if($value->influenza_ninos < 0){
                $log.= "<b>Error AQ".$key."</b> No debe estar vacia. <br>";
            }
            if($value->fiebre_amarilla_ninos_de_1_ano < 0){
                $log.= "<b>Error AR".$key."</b> No debe estar vacia. <br>";
            }
            if($value->hepatitis_a < 0){
                $log.= "<b>Error AS".$key."</b> No debe estar vacia. <br>";
            }
            if($value->triple_viral_ninos < 0){
                $log.= "<b>Error AT".$key."</b> No debe estar vacia. <br>";
            }
            if($value->virus_del_papiloma_humano_vph < 0){
                $log.= "<b>Error AU".$key."</b> No debe estar vacia. <br>";
            }
            if($value->td_o_tt_mujeres_en_edad_fertil_15_a_49_anos < 0){
                $log.= "<b>Error AV".$key."</b> No debe estar vacia. <br>";
            }
            if($value->control_de_placa_bacteriana < 0){
                $log.= "<b>Error AW".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_atencion_parto_o_cesarea ? "" : "<b>Error AX".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_salida_de_la_atencion_del_parto_o_cesarea ? "" : "<b>Error AY".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_de_consejeria_en_lactancia_materna ? "" : "<b>Error AZ".$key."</b> No debe estar vacia. <br>";
            $log.= $value->control_recien_nacido ? "" : "<b>Error BA".$key."</b> No debe estar vacia. <br>";
            $log.= $value->planificacion_familiar_primera_vez ? "" : "<b>Error BB".$key."</b> No debe estar vacia. <br>";
            if($value->suministro_de_metodo_anticonceptivo < 0){
                $log.=  "<b>Error BC".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_suministro_de_metodo_anticonceptivo ? "" : "<b>Error BD".$key."</b> No debe estar vacia. <br>";
            $log.= $value->control_prenatal_de_primera_vez ? "" : "<b>Error BE".$key."</b> No debe estar vacia. <br>";
            if($value->control_prenatal < 0){
                $log.=  "<b>Error BF".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->ultimo_control_prenatal ? "" : "<b>Error BG".$key."</b> No debe estar vacia. <br>";
            if($value->suministro_de_acido_folico_en_el_ultimo_control_prenatal < 0){
                $log.= "<b>Error BH".$key."</b> No debe estar vacia. <br>";
            }
            if($value->suministro_de_sulfato_ferroso_en_el_ultimo_control_prenatal < 0){
                $log.= "<b>Error BI".$key."</b> No debe estar vacia. <br>";
            }
            if($value->suministro_de_carbonato_de_calcio_en_el_ultimo_control_prenatal < 0){
                $log.= "<b>Error BJ".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->valoracion_de_la_agudeza_visual ? "" : "<b>Error BK".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consulta_por_oftalmologia ? "" : "<b>Error BL".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_diagnostico_desnutricion_proteico_calorica ? "" : "<b>Error BM".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consulta_mujer_o_menor_victima_del_maltrato ? "" : "<b>Error BN".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consulta_victimas_de_violencia_sexual ? "" : "<b>Error BO".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consulta_nutricion ? "" : "<b>Error BP".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consulta_de_psicologia ? "" : "<b>Error BQ".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consulta_de_crecimiento_y_desarrollo_primera_vez ? "" : "<b>Error BR".$key."</b> No debe estar vacia. <br>";
            if($value->suministro_de_sulfato_ferroso_en_la_ultima_consulta_del_menor_de_10_anos < 0){
                $log.= "<b>Error BS".$key."</b> No debe estar vacia. <br>";
            }
            if($value->suministro_de_vitamina_a_en_la_ultima_consulta_del_menor_de_10_anos < 0){
                $log.= "<b>Error BT".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->consulta_de_joven_primera_vez ? "" : "<b>Error BU".$key."</b> No debe estar vacia. <br>";
            $log.= $value->consulta_de_adulto_primera_vez ? "" : "<b>Error BV".$key."</b> No debe estar vacia. <br>";
            if($value->preservativos_entregados_a_pacientes_con_its < 0){
                $log.= "<b>Error BW".$key."</b> No debe estar vacia. <br>";;
            }
            $log.= $value->asesoria_pre_test_elisa_para_vih ? "" : "<b>Error BX".$key."</b> No debe estar vacia. <br>";
            $log.= $value->asesoria_pos_test_elisa_para_vih ? "" : "<b>Error BY".$key."</b> No debe estar vacia. <br>";
            if($value->paciente_con_diagnostico_de_ansiedad_depresion_esquizofrenia_deficit_de_atencion_consumo_spa_y_bipolaridad_recibio_atencion_en_los_ultimos_6_meses_por_equipo_interdisciplinario_completo < 0){
                $log.= "<b>Error BZ".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_antigeno_de_superficie_hepatitis_b_en_gestantes ? "" : "<b>Error CA".$key."</b> No debe estar vacia. <br>";
            if($value->resultado_antigeno_de_superficie_hepatitis_b_en_gestantes < 0){
                $log.= "<b>Error CB".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_serologia_para_sifilis ? "" : "<b>Error CC".$key."</b> No debe estar vacia. <br>";
            if($value->resultado_serologia_para_sifilis < 0){
                $log.= "<b>Error CD".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_de_toma_de_elisa_para_vih ? "" : "<b>Error CE".$key."</b> No debe estar vacia. <br>";
            if($value->resultado_elisa_para_vih < 0){
                $log.= "<b>Error CF".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_tsh_neonatal ? "" : "<b>Error CG".$key."</b> No debe estar vacia. <br>";
            if($value->resultado_de_tsh_neonatal < 0){
                $log.= "<b>Error CH".$key."</b> No debe estar vacia. <br>";
            }
            if($value->tamizaje_cancer_de_cuello_uterino < 0){
                $log.= "<b>Error CI".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->citologia_cervico_uterina ? "" : "<b>Error CJ".$key."</b> No debe estar vacia. <br>";
            if($value->citologia_cervico_uterina_resultados_segun_bethesda < 0){
                $log.= "<b>Error CK".$key."</b> No debe estar vacia. <br>";
            }
            if($value->calidad_en_la_muestra_de_citologia_cervicouterina < 0){
                $log.= "<b>Error CL".$key."</b> No debe estar vacia. <br>";
            }
            if($value->codigo_de_habilitacion_ips_donde_se_toma_citologia_cervicouterina < 0){
                $log.= "<b>Error CM".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_colposcopia ? "" : "<b>Error CN".$key."</b> No debe estar vacia. <br>";
            if($value->codigo_de_habilitacion_ips_donde_se_toma_colposcopia < 0){
                $log.= "<b>Error CO".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_biopsia_cervical ? "" : "<b>Error CP".$key."</b> No debe estar vacia. <br>";
            if($value->resultado_de_biopsia_cervical < 0){
                $log.= "<b>Error CQ".$key."</b> No debe estar vacia. <br>";
            }
            if($value->codigo_de_habilitacion_ips_donde_se_toma_biopsia_cervical < 0){
                $log.= "<b>Error CR".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_mamografia ? "" : "<b>Error CS".$key."</b> No debe estar vacia. <br>";
            if($value->resultado_mamografia < 0){
                $log.= "<b>Error CT".$key."</b> No debe estar vacia. <br>";
            }
            if($value->codigo_de_habilitacion_ips_donde_se_toma_mamografia < 0){
                $log.= "<b>Error CU".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_toma_biopsia_seno_por_bacaf ? "" : "<b>Error CV".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_resultado_biopsia_seno_por_bacaf ? "" : "<b>Error CW".$key."</b> No debe estar vacia. <br>";
            if($value->biopsia_seno_por_bacaf < 0){
                $log.= "<b>Error CX".$key."</b> No debe estar vacia. <br>";
            }
            if($value->codigo_de_habilitacion_ips_donde_se_toma_biopsia_seno_por_bacaf < 0){
                $log.= "<b>Error CY".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_toma_de_hemoglobina ? "" : "<b>Error CZ".$key."</b> No debe estar vacia. <br>";
            if($value->hemoglobina < 0){
                $log.= "<b>Error DA".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_de_la_toma_de_glicemia_basal ? "" : "<b>Error DB".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_creatinina ? "" : "<b>Error DC".$key."</b> No debe estar vacia. <br>";
            if($value->creatinina < 0){
                $log.= "<b>Error DD".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_hemoglobina_glicosilada ? "" : "<b>Error DE".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_toma_de_microalbuminuria ? "" : "<b>Error DF".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_toma_de_hdl ? "" : "<b>Error DG".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_toma_de_baciloscopia_de_diagnostico ? "" : "<b>Error DH".$key."</b> No debe estar vacia. <br>";
            $log.= $value->baciloscopia_de_diagnostico ? "" : "<b>Error DI".$key."</b> No debe estar vacia. <br>";
            if($value->tratamiento_para_hipotiroidismo_congenito < 0){
                $log.= "<b>Error DJ".$key."</b> No debe estar vacia. <br>";
            }
            if($value->tratamiento_para_sifilis_gestacional < 0){
                $log.= "<b>Error DK".$key."</b> No debe estar vacia. <br>";
            }
            if($value->tratamiento_para_sifilis_congenita < 0){
                $log.= "<b>Error DL".$key."</b> No debe estar vacia. <br>";
            }
            if($value->tratamiento_para_lepra < 0){
                $log.= "<b>Error DM".$key."</b> No debe estar vacia. <br>";
            }
            $log.= $value->fecha_de_terminacion_tratamiento_para_leishmaniasis ? "" : "<b>Error DN".$key."</b> No debe estar vacia. <br>";
            $log.= $value->estado ? "" : "<b>Error DO".$key."</b> No debe estar vacia. <br>";
            $log.= $value->entidad ? "" : "<b>Error DP".$key."</b> No debe estar vacia. <br>";
            $log.= $value->regimen ? "" : "<b>Error DQ".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_de_afiliacion_efectiva ? "" : "<b>Error DR".$key."</b> No debe estar vacia. <br>";
            $log.= $value->fecha_de_finalizacion_de_afiliacion ? "" : "<b>Error DS".$key."</b> No debe estar vacia. <br>";
            $log.= $value->tipo_de_afiliado ? "" : "<b>Error DT".$key."</b> No debe estar vacia. <br>";
            $log.= $value->departamento ? "" : "<b>Error DU".$key."</b> No debe estar vacia. <br>";
            $log.= $value->municipio ? "" : "<b>Error DV".$key."</b> No debe estar vacia. <br>";
            $log.= $value->obeservaciones_01 ? "" : "<b>Error DW".$key."</b> No debe estar vacia. <br>";
            $log.= $value->obeservaciones_02 ? "" : "<b>Error DX".$key."</b> No debe estar vacia. <br>";
        }
        return $log;
    }
    public function descargarLog(Request $request){
        $text = '';
        $text.= "<h2>Gobernación de Cundinamarca | Resultados .:. Prevalidador 4505</h2>";
        $text.= "<p> A continuación encontrará el resultado del prevalidador 4505, lea atentamente el registro de errores que se presenta a continuación. Si tiene alguna duda con el proceso, no dude en contactarnos a las líneas de ayuda. <hr>";
        $log = $request->input('log');
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($text.$log);
        return $pdf->stream('invoice');
    }
}