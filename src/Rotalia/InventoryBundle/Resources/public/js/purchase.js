/**
 * BasketItem class
 * @param id
 * @param text
 * @param price
 * @param amount
 * @constructor
 */
var BasketItem = function (id, text, price, amount) {
    var $this = $(this);
    $this.id = id;
    $this.text = text;
    $this.price = price;
    $this.amount = amount;

    var setAmount = function(amount) {
        $this.amount = amount;
    }
};

/**
 * Basket class
 * @constructor
 */
var Basket = function() {
    var $this = $(this);
    $this.items = [];

    var addItem = function(item) {
        $this.items[$this.items.length] = item;
    };

    var save = function() {
        localStorage.setItem("basket", $this);
    };

    var draw = function() {
        //TODO
    }
};

var basket;


$(function() {
    //Get Basket from local storage or create new
    if (localStorage.getItem("basket")) {
        basket = localStorage.getItem("basket");
    } else {
        basket = Basket();
    }

    $("#ProductFilterType_product_id").on("select2:select", function() {
        var $this = $(this);
        var data = $this.select2('data');
        var price = data[0].price;
        $this.closest('tr').find('.price-col').html(price);
    }).on("select2:unselect", function() {
        var $this = $(this);
        $this.closest('tr').find('.price-col').html('-');
    });
});
