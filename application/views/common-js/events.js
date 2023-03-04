class events {
    static _validateAndTrigger(event, data = '')
    {
        if (typeof fbPixel !== 'undefined' && true == fbPixel) {
            fbq('track', event, data);
        }  
    }

    static addToCart()
    {
        this._validateAndTrigger('AddToCart');
    }

    static addToWishList()
    {
        this._validateAndTrigger('AddToWishlist');
    }

    static contactUs()
    {
        this._validateAndTrigger('Contact');
    }

    static customizeProduct()
    {
        this._validateAndTrigger('CustomizeProduct');
    }

    static initiateCheckout()
    {
        this._validateAndTrigger('InitiateCheckout');
    }

    static purchase(data)
    {
        this._validateAndTrigger('Purchase', data);
    }

    static search()
    {
        this._validateAndTrigger('Search');
    }

    /* 
        A visit to a web page you care about. For example, a product or landing page. View content tells you if someone visits a web page's URL, but not what they do or see on that web page.
    */
    static viewContent()
    {
        this._validateAndTrigger('ViewContent');
    }

    static newsLetterSubscription()
    {
        this._validateAndTrigger('CompleteRegistration');
    }
}
