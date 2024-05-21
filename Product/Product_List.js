// Function for sorting products by Name/Price
function sortProducts() {
  var select = document.getElementById("sortSelect");
  var selectedValue = select.options[select.selectedIndex].value;

  if (selectedValue !== "none") {
    var column = selectedValue.split("_")[0];
    var descending = selectedValue.split("_")[1] === "desc";
    sort(column, descending);
  }
}


// Helper function to perform sorting
// Helper function to perform sorting
function sort(column, descending = false) {
  var table, rows, switching, i, shouldSwitch;
  table = document.getElementById("productTable");
  switching = true;

  while (switching) {
    switching = false;
    rows = table.rows;

    for (i = 1; i < rows.length - 1; i++) {
      shouldSwitch = false;
      var x = rows[i].getElementsByTagName("TD")[getColumnIndex(column)].innerText.trim();
      var y = rows[i + 1].getElementsByTagName("TD")[getColumnIndex(column)].innerText.trim();

      // Convert prices to numbers for comparison
      if (column === "price") {
        x = parseFloat(x.replace(/[^0-9.-]+/g, ""));
        y = parseFloat(y.replace(/[^0-9.-]+/g, ""));
      }

      // Compare values and handle descending order
      if ((!descending && x > y) || (descending && x < y)) {
        shouldSwitch = true;
        break;
      }
    }

    // Swap rows if necessary
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}





// Function to get the index of the column to be sorted
function getColumnIndex(column) {
  switch (column) {
    case "price":
      return 6; // Index of Selling Price column
    case "name":
      return 2; // Index of Product Name column
    default:
      return 1; // Default to the second column (Product ID)
  }
}


// Function to handle single show/hide button for all selected products
function toggleVisibilityForSelected(visibility) {
  var form = document.getElementById("productForm");
  var checkboxes = document.querySelectorAll(
    'input[name="selected_products[]"]:checked'
  );
  var selectedProducts = [];

  checkboxes.forEach(function (checkbox) {
    selectedProducts.push(checkbox.value);
  });

  // Create input element for selected products
  var selectedProductsInput = document.createElement("input");
  selectedProductsInput.type = "hidden";
  selectedProductsInput.name = "selected_products[]";
  selectedProductsInput.value = selectedProducts.join(","); // Convert array to comma-separated string
  form.appendChild(selectedProductsInput);

  // Set action for toggling visibility
  var actionInput = document.createElement("input");
  actionInput.type = "hidden";
  actionInput.name = "action";
  actionInput.value = "toggle_visibility";
  form.appendChild(actionInput);

  // Set visibility value
  var visibilityInput = document.createElement("input");
  visibilityInput.type = "hidden";
  visibilityInput.name = "visibility";
  visibilityInput.value = visibility;
  form.appendChild(visibilityInput);

  // Submit the form
  form.submit();
}

// Function to toggle master checkbox
function toggleMasterCheckbox() {
  var masterCheckbox = document.getElementById("masterCheckbox");
  var checkboxes = document.querySelectorAll(
    'input[name="selected_products[]"]'
  );
  checkboxes.forEach(function (checkbox) {
    checkbox.checked = masterCheckbox.checked;
  });
}

/* --------------------------------------------------------------------------------------------------------------*/
function deleteSelectedProducts() {
  var checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
  var ids = [];
  checkboxes.forEach(function (checkbox) {
    ids.push(checkbox.value);
  });

  var confirmation = confirm(
    "Are you sure you want to delete the selected products?\n*Disclaimer* : This action is irreversible"
  );
  if (confirmation) {
    ids.forEach(function (id) {
      deleteProduct(id);
    });
  }
}

function deleteProduct(productId) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        // Success, reload the page or update the UI as needed
        window.location.reload();
      } else {
        // Handle errors
        console.error("Failed to delete product with ID " + productId);
      }
    }
  };
  xhr.open("GET", "Products_Delete.php?id=" + productId, true);
  xhr.send();
}

document.addEventListener("DOMContentLoaded", function () {
  var checkboxes = document.querySelectorAll("input[type='checkbox']");

  var hideButton = document.getElementById("hideButton");
  var showButton = document.getElementById("showButton");
  var deleteButton = document.getElementById("deleteButton");

function updateButtonsState() {
  var checked = false; // Initialize checked flag

  checkboxes.forEach(function (checkbox) {
    if (checkbox.checked) {
      checked = true; // If at least one checkbox is checked, set the flag to true
    }
  });

  if (checked) {
    showButton.disabled = false;
    deleteButton.disabled = false;
    hideButton.disabled = false;
    hideButton.style.opacity = 1;
    showButton.style.opacity = 1;
    deleteButton.style.opacity = 1;
    showButton.style.cursor = "default"; // Set cursor to default
    deleteButton.style.cursor = "default"; // Set cursor to default
    hideButton.style.cursor = "default"; // Set cursor to default
  } else {
    hideButton.disabled = true;
    hideButton.style.opacity = 0.5;
    showButton.disabled = true;
    deleteButton.disabled = true;
    showButton.style.opacity = 0.5;
    deleteButton.style.opacity = 0.5;
    showButton.style.cursor = "default"; // Set cursor to default
    deleteButton.style.cursor = "default"; // Set cursor to default
    hideButton.style.cursor = "default"; // Set cursor to default
  }
}


  checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener("change", updateButtonsState);
  });

  updateButtonsState(); // Initial state check
});


function searchProducts() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("productTable");
  tr = table.getElementsByTagName("tr");

  // Loop through table rows starting from the second row (index 1)
  for (i = 1; i < tr.length; i++) {
      td = tr[i].getElementsByTagName("td")[2]; // Product Name column index is 2
      if (td) {
          txtValue = td.textContent || td.innerText;
          if (txtValue.toUpperCase().indexOf(filter) > -1) {
              tr[i].style.display = "";
          } else {
              tr[i].style.display = "none";
          }
      }
  }
}


// Cart Related Functions --------------------------------------------
function addToCart(productId) {
  
  // Retrieve existing cart items from local storage
  let cart_Products = localStorage.getItem('cart_Products');
  cart_Products = cart_Products ? JSON.parse(cart_Products) : [];

  // Check if the product is already in the cart
  const existingItem = cart_Products.find(item => item.productId === String(productId));

  if (existingItem) {
      // Increment quantity if product is already in the cart
      existingItem.quantity++;
  } else {
      // Add new product to the cart
      cart_Products.push({ productId, quantity: 1 });
  }

  // Store updated cart items back to local storage
  localStorage.setItem('cart_Products', JSON.stringify(cart_Products));

  // Provide visual feedback to the user (optional)
  alert('Product added to cart!');
}

// Attach event listener to all "Add to Cart" buttons on the page
document.addEventListener('DOMContentLoaded', () => {
  // Your code to attach event listeners here
  document.querySelectorAll('.add_To_Cart').forEach(button => {
    button.addEventListener('click', () => {
      const productId = button.dataset.productId;
      addToCart(productId);
      console.log('addToCart() called for product ID:', productId);
    });
  });
});


