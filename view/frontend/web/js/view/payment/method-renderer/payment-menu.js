define([
    'Magento_Checkout/js/view/payment/default',
    'ko',
    'jquery',
    'mage/storage',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'uiRegistry',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/model/messages',
    'uiLayout',
    'Magento_Checkout/js/action/redirect-on-success',
    'PayEx_Checkin/js/action/open-shipping-information',
    'Magento_Checkout/js/model/full-screen-loader',
    'paymentMenuStyling',
    'mage/cookies'
], function (Component, ko, $, storage, placeOrderAction, selectPaymentMethodAction, quote, customer, paymentService, checkoutData, checkoutDataResolver, registry, additionalValidators, Messages, layout, redirectOnSuccessAction, openShippingInformation, fullscreenLoader, paymentMenuStyling) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayEx_PaymentMenu/payment/menu'
        },
        config: {
            data: {
                culture: 'en-US',
                logo: 'PayEx_PaymentMenu/images/payex-logo.png'
            }
        },
        logoUrl: function(){
            return require.toUrl(this.config.data.logo);
        },
        initialize: function() {
            var self = this;
            self.totals = {};
            self.paymentScript = '';

            self._super();
            Object.assign(this.config.data, window.checkoutConfig.PayEx_PaymentMenu);

            quote.totals.subscribe(function(totals){
                if(self.totals.grand_total !== totals.grand_total){
                    if(self.getCode() == self.isChecked()) {
                        self.updatePaymentMenuScript();
                    }
                }

                self.totals = totals;
            });
        },
        clearPaymentMenu: function(){
            if (typeof payex.hostedView.paymentMenu !== "undefined") {
                payex.hostedView.paymentMenu().close();
            }

            $('#paymentMenuScript').remove();
            $('#payex-payment-menu').empty();
        },
        updatePaymentMenuScript: function(){
            let self = this;

            fullscreenLoader.startLoader();

            storage.get(
                self.config.data.onUpdated,
                "",
                true
            ).done(function(response){
                if(self.paymentScript != response.result) {
                    self.clearPaymentMenu();
                    self.renderPaymentMenuScript(response.result);

                    self.paymentScript = response.result;
                    fullscreenLoader.stopLoader();
                }
            }).fail(function(message){
                console.error(message);
                fullscreenLoader.stopLoader();
            });
        },
        renderPaymentMenuScript: function(scriptSrc){
            var self = this;
            var script = document.createElement('script');

            script.type = "text/javascript";
            script.id = "paymentMenuScript";

            $('.checkout-index-index').append(script);

            script.onload = function(){
                if(self.paymentScript == scriptSrc) {
                    self.payexSetupHostedView();
                }
            };

            script.src = scriptSrc;
        },
        payexSetupHostedView: function() {
            payex.hostedView.paymentMenu({
                container: 'payex-payment-menu',
                //culture: this.config.culture,
                style: paymentMenuStyling,
                onPaymentCompleted: this.onPaymentCompleted.bind(this),
                onPaymentFailed: this.onPaymentFailed.bind(this),
                onPaymentCreated: this.onPaymentCreated.bind(this),
                onPaymentToS: this.onPaymentToS.bind(this),
                onPaymentMenuInstrumentSelected: this.onPaymentMenuInstrumentSelected.bind(this),
                onError: this.onError.bind(this),
            }).open();
        },
        onShippingInfoNotValid: function(){
            openShippingInformation.open();
        },
        onPaymentCompleted: function(paymentCompletedEvent) {
            let self = this;
            fullscreenLoader.startLoader();

            storage.post(
                self.config.data.onPaymentCompleted,
                JSON.stringify(paymentCompletedEvent),
                true
            ).done(function(response){
                // On validation error
                if(!self.placeOrder()) {
                    fullscreenLoader.stopLoader();
                    self.updatePaymentMenuScript();
                    self.onShippingInfoNotValid();
                }
            }).fail(function(message){
                console.error(message);
                fullscreenLoader.stopLoader();
            });
        },
        onPaymentFailed: function(paymentFailedEvent) {
            let self = this;

            storage.post(
                self.config.data.onPaymentFailed,
                JSON.stringify(paymentFailedEvent),
                true
            ).done(function(response){
                console.log(response);
            }).fail(function(message){
                console.error(message);
            });
        },
        onPaymentCreated: function(paymentCreatedEvent) {
            let self = this;

            storage.post(
                self.config.data.onPaymentCreated,
                JSON.stringify(paymentCreatedEvent),
                true
            ).done(function(response){
                console.log(response);
            }).fail(function(message){
                console.error(message);
            });
        },
        onPaymentToS: function(paymentToSEvent) {
            let self = this;

            storage.post(
                self.config.data.onPaymentToS,
                JSON.stringify(paymentToSEvent),
                true
            ).done(function(response){
                console.log(response);
                window.open(response.openUrl, '_blank');
            }).fail(function(message){
                console.error(message);
            });
        },
        onPaymentMenuInstrumentSelected: function(paymentMenuInstrumentSelectedEvent) {
            let self = this;

            storage.post(
                self.config.data.onPaymentMenuInstrumentSelected,
                JSON.stringify(paymentMenuInstrumentSelectedEvent),
                true
            ).done(function(response){
            }).fail(function(message){
                console.error(message);
            });
        },
        onError: function(error) {
            let self = this;

            storage.post(
                self.config.data.onPaymentError,
                JSON.stringify(error),
                true
            ).done(function(response){
                console.log(response);
            }).fail(function(message){
                console.error(message);
            });
        }
        
    });
});
