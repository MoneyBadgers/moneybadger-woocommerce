(function () {
    "use strict";

    // Aliases for window objects
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
    const { createElement } = window.wp.element;
    const { decodeEntities } = window.wp.htmlEntities;
    const { __ } = window.wp.i18n;
    const { getSetting } = window.wc.wcSettings;

    // Function to get MoneyBadger settings
    const getMoneyBadgerSettings = () => {
        const settings = getSetting("wc_moneybadger_payment_gateway_data", null);
        if (!settings) throw new Error("MoneyBadger initialization data is not available");
        return settings;
    };

    // Function to get the content description
    const getContentDescription = () => decodeEntities(getMoneyBadgerSettings()?.description || "");

    // Get supported features
    const supportedFeatures = getMoneyBadgerSettings()?.supports || [];

    // Creating the label with the text and logo
    const moneyBadgerLabel = createElement(
        'span',
        { className: 'moneybadger-label', style: { display: 'flex', alignItems: 'center' } },
        createElement('span', { style: { marginRight: '10px' } }, 'MoneyBadger'),
        createElement('img', {
            src: getMoneyBadgerSettings()?.logo_url,
            alt: 'MoneyBadger Icon',
            style: { width: '50px', height: 'auto' }
        })
    );

    // Register the MoneyBadger payment method
    registerPaymentMethod({
        name: "wc_moneybadger_payment_gateway",
        label: moneyBadgerLabel,
        description: 'Pay with MoneyBadger',
        title: createElement('span', { className: 'moneybadger-title', style: { display: 'flex', alignItems: 'center' } },
            createElement('span', { style: { marginRight: '10%' } }, 'MoneyBadger'),
            createElement('img', {
                src: getMoneyBadgerSettings()?.logo_url,
                alt: 'MoneyBadger Icon',
                style: { width: '50%', height: 'auto' }
            })
        ),
        icon: createElement(
            'img',
            {
                src: getMoneyBadgerSettings()?.logo_url,
                alt: 'MoneyBadger Icon',
                style: { width: '50%' }
            }),
        canMakePayment: () => true,
        ariaLabel: __("MoneyBadger payment method", "wc_moneybadger_payment_gateway"),
        content: createElement(getContentDescription, null),
        edit: createElement(getContentDescription, null),
        supports: {
            features: supportedFeatures,
        },
    });
})();
