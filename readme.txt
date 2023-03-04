
Multivendor - Released Version : RV-9.3.1
    => IOS/Android Buyer APP version : 2.0.1
    => System API version : 2.3

New Feature : 
    => 82248 : DPO Payment Gateway Integration

Updates/Fixes : 
    => 055995 - When user close the browser at payment screen same order is assigned
    => 056192 - Showing fatal error while opening settings of split payment method.
    => 056225 - The settings are not getting saved for the related product settings under Admin dashboard
    => 056373 - if admin shipping is enabled then even seller is credited with shipping amount
    => 056508 - on order cancellation requests error coming at admin side
    => 056555 - Unable to view products when seller has pending subscription
    => 056568 - on admin seller product left navigation wrong seller order count
    => 056585 - on manage collections Banner layout1 and layout2 has wrong image of layout
    => 056944 - on saving seller catalog specification at admin end error coming
    => 056959 - on updating seller order getting error at admin end 
    => 057017 - importing seller products in german getting error invalid csv column
    => 057089 - Admin: Suggestion to override the images while uploading multiple images because we are showing only one image for the Brands at the buyers end
    => 057115 - Seller and Admin: "+" button is not showing while adding the pickup address' for "All days" Time Slots
    => 057163 - while adding the option in catalog getting struck in ajax loop
    => 057444 - If brand is mandatory then while importing getting error
    => 057684 - in apple app mp4 tried to run instead of download
    => 057725 - Multiple first time purchase discount coupons are getting created by the system for one user
    => 057749 - Category with no product is also listing on view all category page
    => 057771 - if shipping api is enabled the there is no need of shipping profile on catalog
    => 057783 - while exporting file in russin language file name coming with unwanted string
    => 057884 - On page pages generic location label used
    => 057956 - importing category enable/disable issue 
    => 058034 - in blank db dummy data exist
    => 058142 - we should have zone export/import
    => 058197 - on email header logo static link is there
    => 058449 - import Product seo data not working 
    => 058462 - on product detail page specification not coming with group 
    => 058450 - product stock availability issue
    => 058557 - pagination issue in admin seller product pages
    => 058613 - Fullfilment on shop form should be mandatory fields.
    => 058768 - if category identifier contain apostrophe then filter not coming on category page
    => 058807 - Unable to change the URL for a sellers collection    
    => 059263 - On order product search with lang there is query issue 
    => 059798 - invalid mime type error coming while uploading doc file in blog contribution
    => 059422 - Seller: Admin disable the "Allow Sellers To Add Products" option and then, "Add Seller's Shipping profile" option is not showing to add the shipping for the marketplace product
    => 059821 - new tag unable to add in catalog
    => 059913 - on home page favorite icon not updating
    => 060149 - When we add collection banner from admin side then product banner is deleting
    => 060567 - When all inventory created for product getting error on form
    => 060820 - On order subscription getting error
    => 060935 - Admin: "My Products" option is showing on the Dashboard section at the seller's panel(Admin disable the "Allow Seller to Add product" option
    => 060937 - Admin: "Country Code" is not showing correctly under the 'Seller Approval form' section
    => 060944 - Seller: Getting a double error message while trying to access the Direct URL to create custom-products and able to access the product's page (Admin disable the "Allow Seller to Add product" option 
    => 060956 - Stripe Connect: Handling a Standard connect account type previously exists in the system
    => 061359 - while product search as per current delivery location custom product coming out of stock
    => 061561 - while purchasing subscription package 2 orders created
    => 061801 - Coupen not displaying to other user once put on pending order by other user
    => 061865 - The Payment method's name is showing on the subscription view order page instead of Payment Method label.
    => 062159 - Fix wallet payment issue from mobile
    => 062213 - View order from application order total amount is wrong when currency other than system
    => 062225 - on seller product form copy function only working on first feild
    => 062351 - Review Cart > Product level shipping layout > Seller product price and shipping price is invalid.
    => 062446 - Admin: "Reset editor content to default" functionality is not working properly in the content blocks page under the CMS section
    => 062606 - on checkout app page base payment current amount not coming
    => 062336 - iOS: When the user tries to remove the reward points, Incorrect message is coming. [APP]
    => 062668 - Stripe pay won't charge correctly if payment amount belongs to Zero Decimal currency
    => 062801 - On product image upload and remove product product_img_updated_on not updating
    => 062994 - unable to set shipment method incase of shipping plugin
    => 063118 - Admin>CMS>Collection Management>FAQs>Edit>Link Records listing deaacted or inactive category faq list coming 
    => 063239 - Buyer: The 404 error is showing after clicking on the "Print" option after placing an order
    => 063240 - Buyer: All the "tax Charges" are not showing in the Invoice of the Buyer on the order's details page
    => 063264 - Admin: "Upload media" functionality is not working properly under the category's request section at the admin's end
    => 063273 - Buyer: "Strikethrough" is showing for the discounted percentage while placing an order for the special price product
    => 063292 - Buyer: System images' listing is not showing while the seller trying to upload the profile image 
    => 063304 - Admin: The "country code" label is showing with the phone number while viewing the buyer/seller signup, advertiser signup, and affiliate signup on the Dashboard at the admin's end
    => 063320 - Admin: Getting a UI issue while editing the pickup addresses at the admin's end
    => 063239 - Buyer: The 404 error is showing after clicking on the "Print" option after placing an order
    => 063240 - Buyer: All the "tax Charges" are not showing in the Invoice of the Buyer on the order's details page
    => 063264 - Admin: "Upload media" functionality is not working properly under the category's request section at the admin's end
    => 063273 - Buyer: "Strikethrough" is showing for the discounted percentage while placing an order for the special price product
    => 063292 - Buyer: System images' listing is not showing while the seller trying to upload the profile image 
    => 063304 - Admin: The "country code" label is showing with the phone number while viewing the buyer/seller signup, advertiser signup, and affiliate signup on the Dashboard at the admin's end
    => 063320 - Admin: Getting a UI issue while editing the pickup addresses at the admin's end
    => 063373 - When cod, banktransfer activated wallet pay giving error
    => 063391 - Seller: Discount percentage is not showing correctly while editing the special price at the seller's end
    => 063571 - Stripe connect settings to check whether it is mandatory to configure for seller or not.
    => 063799 - if user transcation list is large then it slows down the page
    => 064255 - https redirect issue while aws 
    => 064308 - while selecting language over front-end getting error
    
Enhancements :
   => Make provision to made seller
   => At shop level  pickup interval option given
   => Tracking order with Google Analytics ecommerce 
   => W3c validator.
   => Performance optimization   
    
Known Issues and Problems :
    => 82248 : Renaming existing DPO Payment Gateway to Paygate as it belongs to South Africa linked with Dpo Group.

Following is a list of known errors that don’t have a workaround. These issues will be fixed in the subsequent release. 
        => Change in minimum selling price when reconfigured by Admin
        => Safari and IE 11 do not support our CSS. More info can be found at https://developer.microsoft.com/en-us/microsoft-edge/platform/status/csslevel3attrfunction/
        => System does not support Zero decimal currency while checking out with stripe

Installation steps:
 	• Download the files and configured with your development/production environment.
 	• You can get all the files mentioned in .gitignore file from git-ignored-files directory.
 	• Renamed -.htaccess file to .htaccess from {document root} and {document root}/public directory
	• Upload Fatbit library and licence files under {document root}/library.
	• Define DB configuration under {document root}/public/settings.php
	• Update basic configuration as per your system requirements under {document root}/conf directory.

Notes:
    Procedures : 
        Execute "{siteurl}/admin/admin-users/create-procedures" is mandatory.
        
    Composer :

        => Composer should be installed on server to run the stripe connect module: composer.json on root of the project has details to download the required libraries in root's vendor folder.
        => Run command "composer update" at root of the project to update composer and fetch all dependennt libraries: 

    Stripe Connect Installation :

        => Required to configure callback url as "{domain-name}/public/index.php?url=stripe-connect/callback" inside stripe's web master's account under https://dashboard.stripe.com/settings/applications under "Integration" -> "Redirects"
        =>  Setup webhook Stripe Connect  https://dashboard.stripe.com/test/webhooks . 
                i) Add Webhook url under "Endpoints receiving events from your account" 
                    1) "Webhook Detail" > Url as "{domain-name}/stripe-connect-pay/payment-status" bind events "payment_intent.payment_failed", "payment_intent.succeeded".
   
    Default Shipping profile setup:
       
       To Bind Products and Zones To Default Shipping Profile, Open <site-url>/admin/patch-update/update-shipping-profiles
       To Bind Zero Tax category as default if "Rest Of The World" country is not bind,Open <site-url>/admin/patch-update/update-tax-rules
       To Update state code which for only state which is present in old database state table, execute update_state_codes.sql  (Mostly done when upgrading form V9.2 to 9.3 )       

    Please replace tbl_countries, tbl_states from db_withdata.sql.

    Please hit <site-url>/admin/patch-update/update-category-relations to update all parent to child level relations in case of updating db.

    s3 bucket notes for bulk media:
        => Create a Lambda function.
        => Add trigers and upload zip file from  git-ignored-files/user-uploads/lib-files/fatbit-s3-zip-extractor.zip
        => Set permission and update Resource based on function created by you.
        {
            "Version": "2012-10-17",
            "Statement": [
                {
                    "Effect": "Allow",
                    "Action": "logs:CreateLogGroup",
                    "Resource": "arn:aws:logs:us-east-2:765751105868:*"
                },
                {
                    "Effect": "Allow",
                    "Action": [
                        "logs:CreateLogStream",
                        "logs:PutLogEvents"
                    ],
                    "Resource": "arn:aws:logs:*:*:*"
                },
                {
                    "Effect": "Allow",
                    "Action": [
                        "s3:PutObject",
                        "s3:GetObject",
                        "s3:DeleteObject"
                    ],
                    "Resource": [
                        "*"
                    ]
                }
            ]
        }

    2Checkout Payment Gateway:
        To Test Sandbox Payment Refer This: https://knowledgecenter.2checkout.com/Documentation/09Test_ordering_system/01Test_payment_methods

