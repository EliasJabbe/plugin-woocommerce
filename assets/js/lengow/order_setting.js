/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

function addScoreCarrier(){
    nbs = 0;

    $('.score').removeClass('red').removeClass('green').removeClass('orange');

    $('.sub').each(function() {
        var total = $(this).find('.carrier').length;
        $(this).find('.carrier').each(function(){

            if ($(this).val() !== "") {
                nbs += 1 ;
            }
        });

        $(this).parents('li.lengow_marketplace_carrier').find('.score').html(nbs+' / '+total);

        if (nbs == total){

            $(this).parents('li.lengow_marketplace_carrier').find('.score').addClass('green');
        } else if (nbs <= 1){

            $(this).parents('li.lengow_marketplace_carrier').find('.score').addClass('red');
        } else {

            $(this).parents('li.lengow_marketplace_carrier').find('.score').addClass('orange');
        }
        nbs = 0;

    });
}

(function ($) {

    $(document).ready(function () {

        addScoreCarrier();

        function changeStockMP() {
            var selector = $('.lengow_import_stock_ship_mp');
            if ($("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").prop('checked')) {
                selector.slideDown(150);
                var divLegend = selector.next('.legend');
                    divLegend.addClass("blue-frame");
                    divLegend.css('display', 'block');
                    divLegend.show();
            } else {
                selector.slideUp(150);
                selector.next('.legend').hide();
            }
        }

        changeStockMP();

        $('#lengow_form_order_setting').on('click', '.add_lengow_default_carrier', function () {
            if ($('#select_country').val() !== "") {
                var href = $(this).attr('data-href');
                var data = {action: 'add_country', id_country: $('#select_country').val()};

                $.getJSON(href, data, function(content) {
                    $("#marketplace_country").append(content['marketplace_carrier']);
                    $("#select_country").html(content['countries']);
                    addScoreCarrier();
                    lengow_jquery('.lengow_select').select2({
                        minimumResultsForSearch: 16,
                        templateResult: formatState
                    });
                    $('.add-country').show();
                });
                $('#error_select_country').html('');
                $('.select_country').hide();
                $(this).addClass('lgw-btn-disabled');
            } else {
                $('#error_select_country').html('<span>No country selected.</span>');
            }
            return false;
        });

        // Change country

        $('#select_country').change(function(){
            $('.add_lengow_default_carrier').removeClass('lgw-btn-disabled');
        });

        $('.js-cancel-country').click(function(){
            $('.select_country').hide();
            $('.add-country').show();
            return false;
        });

        $('#marketplace_country').on('click', '.delete_lengow_default_carrier', function () {
            $(this).closest('.country').addClass('js-confirm');
            return false;
        });

        // CONFIRM REMOVE COUNTRY ? --> NO
        $('#marketplace_country').on('click', '.js-delete-country-no', function () {
            $(this).closest('.country').removeClass('js-confirm');
            return false;
        });

        // CONFIRM REMOVE COUNTRY ? --> YES
        $('#marketplace_country').on('click', '.js-delete-country-yes', function () {
            var href = $('.lengow_default_carrier').attr('data-href');
            var idCountry = $(this).closest('.country').find('.delete_lengow_default_carrier').data('id-country');
            var data = {action: 'delete_country', id_country: idCountry};

            $.getJSON(href, data, function(content) {
                $("#select_country").html(content['countries']);
                $("#lengow_marketplace_carrier_country_" + content['id_country']).remove();
            });
            return false;
        });

        $('#add_marketplace_country').on('change', '.carrier', function () {
            if ($(this).val() !== "") {
                $(this).parents('.add_country').removeClass('no_carrier');
                addScoreCarrier();
            } else {
                $(this).parents('.add_country').addClass('no_carrier');
                addScoreCarrier();
            }
            return false;

        });

        $('#add_marketplace_country').on('change', '.carrier', function () {
            if ($(this).val() !== "") {
                $(this).parents('.marketplace_carrier ').removeClass('no_carrier');
                addScoreCarrier();
            } else {
                $(this).parents('.marketplace_carrier ').addClass('no_carrier');
                addScoreCarrier();
            }
            return false;

        });

        $('.add-country').click( function(){
            $('.add-country').hide();
            $('.select_country').show();
            return false;
        });


        // Toggle countries

        toggleCountry( $('#lengow_form_order_setting .lengow_marketplace_carrier:eq(0)') ); // First one
        $("#lengow_form_order_setting").on('click', '.country',function(){
            toggleCountry( $(this).closest('.lengow_marketplace_carrier') );
        });

        function toggleCountry($head){
            var $sub = $head.closest('li').find('.sub');
            $head.toggleClass('active');
            $head.find('.fa').toggleClass('fa-chevron-down fa-chevron-up');
            $sub.slideToggle(150);
        }

        $("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").on('change', function () {
            changeStockMP();
        });

        // Submit form

        $('#lengow_form_order_setting').submit(function( event ) {
            event.preventDefault();
            var sendForm = true;
            var form = this;

            $("li.add_country .carrier").each(function() {
                // If Carrier not fill
                if ($(this).val() == "") {
                    sendForm = false;
                    $(this).parents(".sub").show();
                    $('html, body').stop().animate({scrollTop: $(this).parents(".has-sub").offset().top - 200}, 100);
                    $(this).parents(".sub").find('.default_carrier_missing').show();
                }
            });

            if(sendForm == true){
                $('#lengow_form_order_setting button[type="submit"]').addClass('loading');
                setTimeout(function () {
                    $('#lengow_form_order_setting button[type="submit"]').removeClass('loading');
                    $('#lengow_form_order_setting button[type="submit"]').addClass('success');
                    form.submit();
                }, 1000);
            }

        });

        function formatState (state) {
            var image = $(state.element).data('image');
            if (!state.id) { return state.text; }
            if (!image) {
                return state.text;
            } else {
                var $state = $(
                    '<span><img width="22" height="15" src="'+ image +'" class="img-flag" /> ' + state.text + '</span>'
                );
                return $state;
            }
        };

        $(".lengow_select").select2({
            templateResult: formatState
        });
    });

})(lengow_jquery);
