<script>
    $(document).on('click','.addToCart',function (){
        let productId = $(this).data('id');
        let ordernow = $(this).data('ordernow');


        let quantity =  Number($('.detailsPageQuantity').text());
        if(!quantity){
            quantity = 1;
        }
        $.ajax({
            url: "{{ route('addToCart') }}",
            method: "post",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                product_id : productId,
                quantity: quantity
            },
            success: function (res) {
                cartLabel(res.data)
                if(res.discount){
                    cartFunction(res.data,res.discount.discountPrice)
                    $('.discountPrice').text(res.discount.discount)
                }else {
                    cartFunction(res.data)
                }
                $('.subTotalPrice').text(
                    currencyPosition(calculateTotalPrice(Object.values(res.data)))
                );

                if(ordernow){
                    window.location.href = '{{route('checkout')}}'
                }
                Notiflix.Notify.success("Item added on the cart")
            },
            error: function (err) {
                Notiflix.Notify.failure(err.responseJSON.message)
            }
        });
    })


    $(document).on('click', '.removeCartItem', function () {
        let productId = $(this).data('id');

        $.ajax({
            url: "{{ route('removeToCart') }}",
            method: "delete",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                product_id: productId
            },
            success: function (res) {
                // Update cart label
                cartLabel(res.data);

                // Remove the cart item
                $(`#cartItem${productId}`).remove();
                $(`#cartItems${productId}`).remove();

                // Check if a discount is present and update price fields accordingly
                let totalPrice = calculateTotalPrice(Object.values(res.data));
                let vat = calculateVat(totalPrice);
                let discount = res.discount?.discount || 0;

                if (res.discount) {
                    $('.totalPrice').text(res.discount.discountPrice);
                    $('.discountPrice').text(discount);
                    let discountPrice = res.discount?.discountWithOutCurrency || 0;
                    $('.totalPriceIncludingVat').text(currencyPosition((totalPrice + vat) - discountPrice));
                } else {
                    $('.totalPrice').text(currencyPosition(totalPrice));
                    $('.totalPriceIncludingVat').text(currencyPosition(totalPrice + vat));
                }

                // Update the subtotal price
                $('.subTotalPrice').text(currencyPosition(totalPrice));

                // Check if the cart is empty and update the HTML accordingly
                if (res.data.length <= 0) {
                    let emptyHtml = `
                    <li class="search-popup-empty">
                        <div class="empty-image">
                            <img src="{{ asset($themeTrue . 'images/empty.png') }}" alt="">
                            <h5>No Item Added to the Cart</h5>
                        </div>
                    </li>`;
                    $('#showHtml').html(emptyHtml);
                }

                // Show a success notification
                Notiflix.Notify.success("Cart item removed successfully");
            },
            error: function (err) {
                // Show an error notification
                Notiflix.Notify.failure(err.responseJSON.message);
            }
        });
    });


    function cartFunction(data,discount = null) {
        const allPrice = Object.values(data)
        $('#showHtml').html(``);
        var html = [];
        Object.keys(data).forEach(function (key) {
            $(`#cartItems${data[key].id}`)
                .find('.sub-total') // Find the child element with the class `.serch-bag-amount`
                .html(`<h6>${currencyPosition(data[key].quantity * data[key].price)}</h6>`);


            html += `<li class="search-bag-items" id="cartItem${data[key].id}">
                <div class="search-bag-content">
                    <div class="search-bag-image">
                        <img src="${data[key].image}" alt="product">
                    </div>
                    <div class="search-bag-title">
                        <h6>${data[key].name}</h6>
                        <div class="search-bag-count">
                            <p>${currencyPosition(data[key].price)}/${data[key].product_quantity + ' ' + data[key].quantity_unit}</p>
                            <div class="incriment-dicriment">
                                <div class="count-single">
                                    <button type="button"  class="decrement" data-id="${data[key].id}"><i class="fa-light fa-minus"></i></button>
                                    <span class="number" id="no${data[key].id}">${data[key].quantity}</span>
                                    <button type="button" class="increment" data-id="${data[key].id}"><i class="fa-light fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="serch-bag-amount">
                    <h6>${currencyPosition(data[key].quantity * data[key].price)}</h6>
                </div>
                <div class="serch-bag-close removeCartItem" data-id="${data[key].id}">
                    <div class="close-btn">
                        <i class="fa-regular fa-xmark"></i>
                    </div>
                </div>
            </li>`;
        });


        if(discount){
            $('.totalPrice').text(
                discount
            );

        }else {
            $('.totalPrice').text(
                currencyPosition(calculateTotalPrice(allPrice))
            );
        }
        $('#showHtml').append(html);
    }

    function calculateTotalPrice(data) {
        let totalPrice = 0;
        for (let i = 0; i < data.length; i++) {
            const item = data[i];
            const price = item.price;
            const quantity = item.quantity;
            totalPrice += price * quantity;
        }
        return totalPrice;
    }

    function cartLabel(data) {
        const cartData = Object.values(data);
        const totalQuantity = cartData.reduce((prev, pres) => {
            return prev + parseInt(pres.quantity)
        }, 0)
        $('.cartItems').text(cartData.length);

    }
    $(document).on('click','.decrement',function (){
        let productId = $(this).data('id');
        let currentQuantity = parseInt($(this).closest('.count-single').find('.number').text());
        if(currentQuantity > 1){
            $(this).closest('.count-single').find('.number').text(currentQuantity-1);
            if(!productId){
                return ;
            }
            updateQuantity(currentQuantity-1 ,productId)
        }
    })


    $(document).on('click','.increment',function (){
        let productId = $(this).data('id');
        let quantity = parseInt($(this).closest('.count-single').find('.number').text());
        $(this).closest('.count-single').find('.number').text(quantity+1)
        if(!productId){
            return ;
        }
        updateQuantity(quantity+1 ,productId)
    })



    function updateQuantity(quantity, productId) {
        $.ajax({
            url: "{{ route('cartUpdate') }}",
            method: "put",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                product_id: productId,
                quantity: quantity
            },
            success: function (res) {
                // Update cart label
                cartLabel(res.data);

                // Calculate total price and VAT once to avoid repetition
                let totalPrice = calculateTotalPrice(Object.values(res.data));
                let vat = calculateVat(totalPrice);
                let discount = res.discount?.discount || 0;

                // Check if there's a discount and update fields accordingly
                if (res.discount) {
                    cartFunction(res.data, res.discount.discountPrice);
                    $('.discountPrice').text(discount);
                    let discountPrice = res.discount?.discountWithOutCurrency || 0;
                    $('.totalPriceIncludingVat').text(currencyPosition((totalPrice + vat) - discountPrice));
                } else {
                    cartFunction(res.data);
                    $('.totalPriceIncludingVat').text(currencyPosition(totalPrice + vat));
                }

                // Update subtotal price
                $('.subTotalPrice').text(currencyPosition(totalPrice));

                // Notify success
                Notiflix.Notify.success("Cart updated successfully");
            },
            error: function (err) {
                // Notify error
                Notiflix.Notify.failure(err.responseJSON.message);
            }
        });
    }



    function currencyPosition(amount) {

        var currencyPosition = @json(basicControl()->is_currency_position);
        var has_space_between_currency_and_amount = @json(basicControl()->has_space_between_currency_and_amount);
        var currency_symbol = @json(basicControl()->currency_symbol);
        var base_currency = @json(basicControl()->base_currency);
        amount = parseFloat(amount).toFixed(2);
        if (currencyPosition === 'left' && has_space_between_currency_and_amount) {
            return currency_symbol + '  ' + amount;
        } else if (currencyPosition === 'left' && !has_space_between_currency_and_amount) {
            return currency_symbol + ' ' + amount;
        } else if (currencyPosition === 'right' && has_space_between_currency_and_amount) {
            return amount + '  ' + base_currency;
        } else {
            return amount + '  ' + base_currency;
        }
    }


    function calculateVat(price){
        let vat = "{{basicControl()->vat}}";
        return (vat * price) / 100;
    }

    $(document).on("submit",'#couponForm1', function (e) {
        e.preventDefault();
        applyCoupon($('#coupon1').val())
    })

    $(document).on("submit",'#couponForm', function (e) {
        e.preventDefault();
        applyCoupon($('#coupon').val())
    })

    function applyCoupon(code){
        $.ajax({
            url: "{{route('applyCoupon')}}",
            method: "post",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: "application/json",
            data: JSON.stringify({
                _token: $('meta[name="csrf-token"]').attr('content'),
                coupon: code,
            }),
            success: function (res) {
                $('#coupon-error').text("");
                $('#coupon-error1').text("");

                $('.totalPrice').text(res.discountPrice);
                $('.discountPrice').text(res.discount);
                let vat = calculateVat(res.total);
                let totalPrice = res.discountPrice + res.discount;
                $('.totalPriceIncludingVat').text(currencyPosition((res.total + vat) - res.discountWithOutCurrency));
                Notiflix.Notify.success(`${code} Applied Successful!`);
                $('#coupon').val('');
                $('#coupon1').val('');
            },
            error: function (error) {
                var errorMessage = error.responseJSON.errors.coupon[0];
                $('#coupon-error').text(errorMessage);
                $('#coupon-error1').text(errorMessage);
                $('#coupon').val("");
                $('#coupon1').val("");
            }
        });
    }

    $(document).on('click', '.addToWishlist', function () {
        let url = '{{route('user.addToWishList')}}'
        let id = $(this).data('id');
        let element = $(this);
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: id
            }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response === 1) {
                    element.removeClass('addedWishlist');
                    Notiflix.Notify.success('Wishlist removed successfully');
                } else if (response === 2) {
                    element.addClass('addedWishlist');
                    Notiflix.Notify.success('Wishlist added successfully');
                }
            },
            error: function (err) {
                console.log(err.responseText)
            }
        });
    })


</script>
