(function () {
    "use strict";

    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
    const wpI18n = window.wp.i18n;
    const wpElement = window.wp.element;

    // Correct image path for the logo
    const iconUrl = 'https://images.squarespace-cdn.com/content/v1/6620d639bfab98168109b055/fbd58a15-6762-45fe-9162-287dcc042c6c/Asset+3%405x.png?format=1500w';

    // Creating the label with the text first and a larger image second
    const moneyBadgerLabel = wpElement.createElement(
        'span', 
        { className: 'moneybadger-label' },
        'MoneyBadger',
        wpElement.createElement('img', {
            src: iconUrl,
            alt: 'MoneyBadger Icon',
            style: { width: '80px', marginLeft: '10px', verticalAlign: 'middle' }
        })
    );

    // Creating the title with the text first and a larger image second
    const moneyBadgerTitle = wpElement.createElement(
        'span',
        { className: 'moneybadger-title' },
        'MoneyBadger',
        wpElement.createElement('img', {
            src: iconUrl,
            alt: 'MoneyBadger Icon',
            style: { width: '80px', marginLeft: '10px', verticalAlign: 'middle' }
        })
    );

    registerPaymentMethod({
        id: 'wc_moneybadger_payment_gateway',
        label: moneyBadgerLabel,  
        description: 'Pay with MoneyBadger',
        title: moneyBadgerTitle, 
        icon: wpElement.createElement('img', { src: iconUrl, alt: 'MoneyBadger Icon', style: { width: '80px' } }),
        
        canMakePayment: () => true,
        ariaLabel: wpI18n.__("MoneyBadger payment method", "wc_moneybadger_payment_gateway"),
        content: wpElement.createElement("wc_moneybadger_payment_gateway", null),
        edit: wpElement.createElement("wc_moneybadger_payment_gateway", null),
        name: 'wc_moneybadger_payment_gateway',
    });

})();
