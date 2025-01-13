   <!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <meta name="csrf-token" content="{{ csrf_token() }}">
       <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
       <title>Product Inventory</title>
   </head>
   <body>
       <div class="container">
           <h1>Product Inventory</h1>
           <form id="productForm">
               <div class="form-group">
                   <label for="product_name">Product Name</label>
                   <input type="text" class="form-control" id="product_name" name="product_name" required>
               </div>
               <div class="form-group">
                   <label for="quantity_in_stock">Quantity in Stock</label>
                   <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" required>
               </div>
               <div class="form-group">
                   <label for="price_per_item">Price per Item</label>
                   <input type="number" class="form-control" id="price_per_item" name="price_per_item" step="0.01" required>
               </div>
               <button type="submit" class="btn btn-primary">Submit</button>
           </form>

           <table class="table mt-4">
               <thead>
                   <tr>
                       <th>Product Name</th>
                       <th>Quantity in Stock</th>
                       <th>Price per Item</th>
                       <th>Datetime Submitted</th>
                       <th>Total Value Number</th>
                       <th>Actions</th>
                   </tr>
               </thead>
               <tbody id="productTable">
                   @foreach ($products as $product)
                       <tr data-id="{{ $product->id }}">
                           <td>{{ $product->product_name }}</td>
                           <td>{{ $product->quantity_in_stock }}</td>
                           <td>{{ $product->price_per_item }}</td>
                           <td>{{ $product->created_at }}</td>
                           <td>{{ $product->quantity_in_stock * $product->price_per_item }}</td>
                           <td>
                               <button class="btn btn-warning edit-btn">Edit</button>
                               <button class="btn btn-danger delete-btn">Delete</button>
                           </td>
                       </tr>
                   @endforeach
                   <tr>
                       <td colspan="4">Total</td>
                       <td id="totalValue"></td>
                       <td></td>
                   </tr>
               </tbody>
           </table>
       </div>

       <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
       <script>
           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               }
           });

           $(document).ready(function() {
               $('#productForm').on('submit', function(e) {
                   e.preventDefault();
                   const formData = $(this).serialize();
                   const productId = $(this).data('id'); // Get the product ID if editing

                   if (productId) {
                       // Update existing product
                       $.ajax({
                           type: 'PUT',
                           url: '/products/' + productId,
                           data: formData,
                           success: function(product) {
                               // Update the product row in the table
                               const row = $('#productTable').find(`tr[data-id="${product.id}"]`);
                               row.find('td:nth-child(1)').text(product.product_name);
                               row.find('td:nth-child(2)').text(product.quantity_in_stock);
                               row.find('td:nth-child(3)').text(product.price_per_item);
                               row.find('td:nth-child(5)').text(product.quantity_in_stock * product.price_per_item);
                               
                               // Clear the form fields
                               $('#productForm').trigger('reset').removeData('id');
                           }
                       });
                   } else {
                       // Create new product
                       $.ajax({
                           type: 'POST',
                           url: '/products',
                           data: formData,
                           success: function(product) {
                               // Append new product to table
                               $('#productTable').prepend(`
                                   <tr data-id="${product.id}">
                                       <td>${product.product_name}</td>
                                       <td>${product.quantity_in_stock}</td>
                                       <td>${product.price_per_item}</td>
                                       <td>${product.created_at}</td>
                                       <td>${product.quantity_in_stock * product.price_per_item}</td>
                                       <td>
                                           <button class="btn btn-warning edit-btn">Edit</button>
                                           <button class="btn btn-danger delete-btn">Delete</button>
                                       </td>
                                   </tr>
                               `);
                               updateTotalValue();

                               // Clear the form fields
                               $('#productForm').trigger('reset');
                           }
                       });
                   }
               });

               // Edit button click event
               $(document).on('click', '.edit-btn', function() {
                   const row = $(this).closest('tr');
                   const productId = row.data('id');
                   const productName = row.find('td:nth-child(1)').text();
                   const quantityInStock = row.find('td:nth-child(2)').text();
                   const pricePerItem = row.find('td:nth-child(3)').text();

                   // Populate the form with the existing data
                   $('#product_name').val(productName);
                   $('#quantity_in_stock').val(quantityInStock);
                   $('#price_per_item').val(pricePerItem);
                   $('#productForm').data('id', productId); // Store the product ID for updating
               });

               function updateTotalValue() {
                   let total = 0;
                   $('#productTable tr').each(function() {
                       const value = $(this).find('td:nth-child(5)').text();
                       total += parseFloat(value) || 0;
                   });
                   $('#totalValue').text(total);
               }

               // Add delete functionality here
           });
       </script>
   </body>
   </html>