(function ($) {

    var selectors = {};
    selectors.localidad = '#localidades';
    selectors.calle = 'input.calle';
    selectors.altura = 'input.altura';
    selectors.provincia = '#provincias';
    selectors.departamento = 'select.departamentos';
    selectors.action = 'input.action';
    selectors.provinciaTooltip = 'div.tooltipProvincias';
    selectors.departamentoTooltip = 'div.tooltipDepartamentos';
    selectors.localidadTooltip = 'div.tooltipLocalidades';

    var msgs = {};
    msgs.calle = 'Verifique el nombre de calle';
    msgs.calle_con_acentos = 'El buscador no reconoce vocales acentuadas. Al buscar, no acentúe los nombres';
    msgs.altura = 'Verifique el número de calle';
    msgs.provincia = 'Seleccione una provincia';
    msgs.departamento = 'Seleccione un departamento';
    msgs.localidadTooltip = 'Seleccione localidad';
    msgs.localidadValidation = 'Seleccione una localidad';

    $(window).load(function () {
        // Evita un null en js cuando activa el combo de provincias
        $('#provincias').select2("enable");
    });

    $(document).keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();
            ajax_request('cpa', true, 'html', cpaResult, true);
        }
    });

    $(document).ready(function () {
        $('#provincias').select2();
        $('#provincias').select2("disable");
        $('#localidades').select2({
            data: [{
                id: 'none',
                text: 'Seleccione una localidad'
            }]
        });
        $('#localidades').select2("disable");
        //Peticion Ajax
        $('button').click(function (e) {
            e.preventDefault();
            ajax_request('cpa', true, 'html', cpaResult, true);
        });
    });

    $(document).on('change', '#provincias', function () {
        if (($('#provincias').val() == 'none')) {
            // Si selecciona ninguna provincia
            $('#localidades').select2("disable");
            $('#localidades').select2({
                data: [{
                    id: 'none',
                    text: 'Seleccione una localidad'
                }]
            });
            $('#localidades').select2("val", 'none');
        } else {
            // si no es CABA y selecciono alguna completo depto y localidad con JSON
            if ($('#provincias').val() != 'C') {
                // inhabilita el selector de localidades momentaneamente
                $('#localidades').select2("disable");
                // inicializa el combo de localidades una vez que se selecciono
                // la provincia
                ajax_request('localidades', false, 'JSON', localidadesSelect, false);
            } else {
                $('#localidades').select2({
                    data: [{
                        id: 5001,
                        text: 'Ciudad Autonoma Buenos Aires'
                    }]
                });
                $('#localidades').select2("val", 5001);
                $('#localidades').select2("enable", true);
            }
        }
    });

    function ajax_request(thisaction, validate, dataType, callback, validateCaptcha) {
        var jsonData = {};

        jsonData.action = thisaction;
        jsonData.localidad = $(selectors.localidad).val();
        jsonData.calle = $(selectors.calle).val();
        jsonData.altura = $(selectors.altura).val();
        jsonData.provincia = $(selectors.provincia).val();
        jsonData.departamento = $(selectors.departamento).val();

        var flag = false;
        if (validate) flag = validate_cpa(jsonData, selectors);

        if (!flag) {
            doAjaxPost(
                '/sites/all/modules/custom/ca_forms/api/wsFacade.php', jsonData, dataType, callback, validateCaptcha
            );
            return;
        }
    }

    function str_formatting(str) {
        return str.replace(/\w\S*/g, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

    function localidadesSelect(json) {
        // Handlers para Localidades
        var deferred = $.Deferred();
        var out = '';
        deferred
            .done(function (json) {
                // Al json de la respuesta le agrega un elemento más
                json.push({ id: 'none', nombre: 'Seleccione una localidad', cp: 1 });

                function format(item) {
                    return item.nombre + " (" + item.cp + ")";
                };

                $("#localidades").select2({
                    minimumInputLength: 3,
                    data: {
                        results: json,
                        text: 'nombre'
                    },
                    formatSelection: format,
                    formatResult: format,
                });
            })
            .done(function () {
                $('#localidades').val("none");
            })
            .done(function () {
                $('#localidades').select2("enable");
                $('#recursive').hide();
            })
            .fail(function () {
                $('#recursive').show();
                $('#localidades').select2("disable");
                // hack para evitar respuestas vacias del WS
                // Recursivo para que auto-reintente con los ultimos datos seteados
                ajax_request('localidades', false, 'JSON', localidadesSelect, false);
            });

        // Verifica que tenga una respuesta que no sea vacia (WS no responde)
        if ((!json) || (typeof json === "undefined")) {
            deferred.reject();
        } else {
            deferred.resolve(json);
        }
    }

    // Muestra el resultado del CPA
    function cpaResult(res) {
        $('#resultado').html(res);
    }

    //Helpers
    function validate_cpa(jsonData, selectors) {
        var ruleCalle = /[\u00A1-\u00D0\u00D2-\u00F0\u00F2-\u00FF\']+/i;
        var rulAltura = /^[0-9]{1,10}$/;
        var flag = false;
        if (jsonData.provincia == 'none') {
            show_tooltip(selectors.provinciaTooltip, msgs.provincia);
            flag = true;
        }
        if (!jsonData.calle.length) {
            show_tooltip(selectors.calle, msgs.calle);
            flag = true;
        }
        if (ruleCalle.test(jsonData.calle)) {
            show_tooltip(selectors.calle, msgs.calle_con_acentos);
            flag = true;
        }
        if (!rulAltura.test(jsonData.altura) || !jsonData.altura.length) {
            show_tooltip(selectors.altura, msgs.altura);
            flag = true;
        }
        if (jsonData.localidad == null || jsonData.localidad == msgs.localidadValidation || jsonData.localidad == 'none') {
            show_tooltip(selectors.localidadTooltip, msgs.localidadTooltip);
            flag = true;
        }
        return flag;
    }

})(jQuery);