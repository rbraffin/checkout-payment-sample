const mp = new MercadoPago('APP_USR-6096a634-0b35-452c-94c9-a18adb8ffb15', {
  locale: 'pt-BR'
});

//Handle call to backend and generate preference.
document.getElementById("checkout-btn").addEventListener("click", function() {

  $('#checkout-btn').attr("disabled", true);
  
  var orderData = {
    quantity: document.getElementById("quantity").value,
    title: document.getElementById("product-description").innerHTML,
    description: "Celular de Tienda e-commerce",
    price: document.getElementById("unit-price").innerHTML,
    image: document.getElementById("product-image").src
  };
    
  fetch("/create_preference", {
          method: "POST",
          headers: {
              "Content-Type": "application/json",
              "X-Requested-With": "XMLHttpRequest"
          },
          body: JSON.stringify(orderData),
    })
      .then(function(response) {
          return response.json();
      })
      .then(function(preference) {
        mp.checkout({
          preference: {
              id: preference.id
          },
          render: {
              container: '#button-checkout', // Indica onde o botão de pagamento será exibido
              label: 'Pague a compra', // Muda o texto do botão de pagamento (opcional)
          }
      });
          $(".shopping-cart").fadeOut(500);
          setTimeout(() => {
              $(".container_payment").show(500).fadeIn();
          }, 500);
      })
      .catch(function() {
          alert("Unexpected error");
          $('#checkout-btn').attr("disabled", false);
      });
});

//Create preference when click on checkout button
function createCheckoutButton(preference) {
  var script = document.createElement("script");
  
  // The source domain must be completed according to the site for which you are integrating.
  // For example: for Argentina ".com.ar" or for Brazil ".com.br".
  script.src = "https://www.mercadopago.com.ar/integrations/v1/web-payment-checkout.js";
  script.type = "text/javascript";
  script.dataset.preferenceId = preference;
  document.getElementById("button-checkout").innerHTML = "";
  document.querySelector("#button-checkout").appendChild(script);
}

//Handle price update
function updatePrice() {
  let quantity = document.getElementById("quantity").value;
  let unitPrice = document.getElementById("unit-price").innerHTML;
  let amount = parseInt(unitPrice) * parseInt(quantity);

  document.getElementById("cart-total").innerHTML = "$ " + amount;
  document.getElementById("summary-price").innerHTML = "$ " + unitPrice;
  document.getElementById("summary-quantity").innerHTML = quantity;
  document.getElementById("summary-total").innerHTML = "$ " + amount;
}
document.getElementById("quantity").addEventListener("change", updatePrice);
updatePrice();  

//go back
document.getElementById("go-back").addEventListener("click", function() {
  $(".container_payment").fadeOut(500);
  setTimeout(() => {
      $(".shopping-cart").show(500).fadeIn();
  }, 500);
  $('#checkout-btn').attr("disabled", false);  
});