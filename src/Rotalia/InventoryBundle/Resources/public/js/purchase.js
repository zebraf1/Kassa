var formatter = new Intl.NumberFormat('et-EE', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2
});

/**
 * BasketItem class
 * @param id
 * @param text
 * @param price
 * @param amount
 * @constructor
 */
var BasketItem = function (id, text, price, amount) {
    this.id = parseInt(id);
    this.text = text;
    this.price = parseFloat(price);
    this.amount = parseFloat(amount);
};

/**
 * Basket class
 * @constructor
 */
var Basket = function() {
    this.items = {};
    this.item_ids = [];
};

/**
 * Adds an item to basket, saves and redraws the basket
 * @param item
 */
Basket.prototype.addItem = function(item) {
    // Sum amounts if the product is already in the basket
    if (this.items[item.id]) {
        this.items[item.id].amount = parseFloat(this.items[item.id].amount) + parseFloat(item.amount);
    } else {
        // Add new item
        this.items[item.id] = item;
        this.item_ids[this.item_ids.length] = item.id;
    }

    this.save();
    this.draw();
};

/**
 * Removes an item from the basket, saves and redraws the basket
 * @param id
 */
Basket.prototype.removeItem = function(id) {
    if (this.items[id]) {
        delete this.items[id];
    }

    this.item_ids = this.item_ids.filter(function (itemId) {
        return itemId !== id;
    });

    this.save();
    this.draw();
};

/**
 * Load basket from local storage
 */
Basket.prototype.load = function() {
    //Get Basket from local storage or create new
    if (localStorage.getItem("basket")) {
        try {
            var basketObject = JSON.parse(localStorage.getItem("basket"));
            if (basketObject.item_ids) {
                this.items = basketObject.items;
                this.item_ids = basketObject.item_ids;
                this.sync();
            }
        } catch (e) {
            //Ignore
        }
    }
    basket.draw();
};

/**
 * Save basket to local storage
 */
Basket.prototype.save = function() {
    localStorage.setItem("basket", JSON.stringify(this));
};

/**
 * Draw basket
 */
Basket.prototype.draw = function() {
    var $table = $('#basketTable');

    // Remove old entries
    $table.find('.basketItemRow').remove();

    // Add items
    var $this = this;
    var totalSum = 0;

    $.each(this.item_ids, function(key, id) {
        if (!$this.items[id]) {
            console.log('Missing item from basket with id '+id);
            return;
        }
        var $row = $('<tr class="basketItemRow" data-id="'+id+'"></tr>');
        $row.append($('<td>'+$this.items[id].text+'</td>'));
        $row.append($('<td>'+formatter.format($this.items[id].price)+'</td>'));
        $row.append($('<td><div class="basketItemAmountReduce"/> '+$this.items[id].amount+' <div class="basketItemAmountAdd"/></td>'));
        $row.append($('<td><a href="#" class="removeBasketProduct">Eemalda</a></td>'));
        $table.append($row);

        totalSum += ($this.items[id].price * $this.items[id].amount);
    });

    $('#basketTotal').html(formatter.format(totalSum));

    $('.removeBasketProduct').on('click', function(e) {
        e.preventDefault();
        var id = $(this).closest('.basketItemRow').data('id');
        basket.removeItem(id);
    });

    $('.basketItemAmountAdd').on('click', function(e) {
        e.preventDefault();
        var id = $(this).closest('.basketItemRow').data('id');
        var item = basket.items[id];
        if (item) {
            item.amount = parseFloat(item.amount) + 1;
            basket.save();
            basket.draw();
        }
    });

    $('.basketItemAmountReduce').on('click', function(e) {
        e.preventDefault();
        var id = $(this).closest('.basketItemRow').data('id');
        var item = basket.items[id];
        if (item && item.amount > 1) {
            item.amount = parseFloat(item.amount) - 1;
            basket.save();
            basket.draw();
        }
    });

    console.log('Basket drawn with ' + this.item_ids.length + ' items');
};

Basket.prototype.sync = function() {
    //TODO - refresh BasketItem prices from server, save new data
};


var basket = new Basket();

$(function() {
    var $priceCol = $('#purchasePrice');
    var $amountInput = $('#purchaseAmount');
    var $addProductButton = $('#purchaseAddProduct');
    var $productInputSelect2 = $("#ProductFilterType_product_id");
    var selectedItem = null;

    basket.load();

    $productInputSelect2.on("select2:select", function() {
        var $this = $(this);
        var data = $this.select2('data');
        var price = data[0].price;
        $priceCol.html(price);

        selectedItem = new BasketItem(data[0].id, data[0].text, data[0].price, 0);
    }).on("select2:unselect", function() {
        var $this = $(this);
        $priceCol.html('-');
        selectedItem = null;
    });

    $addProductButton.on('click', function (e) {
        if (selectedItem === null) {
            alert('Vali toode');
            return;
        }

        selectedItem.amount = parseFloat($amountInput.val());

        basket.addItem(selectedItem);
        basket.save();
        // Reset form
        $priceCol.html('-');
        $amountInput.val(1);
        $productInputSelect2.val(null).trigger("change");
    });
});
