/* 
=> Not included lang id as as foreign key constraint 
=> SET FOREIGN_KEY_CHECKS=0; or run with blank DB 
=> Used for ER diagrams
*/

-- --------tbl_abandoned_cart---------
ALTER TABLE `tbl_abandoned_cart` ADD CONSTRAINT `abandonedcart_user_id` FOREIGN KEY (`abandonedcart_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_abandoned_cart` ADD CONSTRAINT `abandonedcart_selprod_id` FOREIGN KEY (`abandonedcart_selprod_id`) REFERENCES `tbl_seller_products`(`selprod_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------tbl_addresses---------
ALTER TABLE `tbl_addresses` ADD CONSTRAINT `addr_state_id` FOREIGN KEY (`addr_state_id`) REFERENCES `tbl_states`(`state_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_addresses` ADD CONSTRAINT `addr_country_id` FOREIGN KEY (`addr_country_id`) REFERENCES `tbl_countries`(`country_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------tbl_admin_auth_token------------------
ALTER TABLE `tbl_admin_auth_token` ADD CONSTRAINT `admrm_admin_id` FOREIGN KEY (`admauth_admin_id`) REFERENCES `tbl_admin`(`admin_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------tbl_admin_password_reset_requests-------------------------------
ALTER TABLE `tbl_admin_password_reset_requests` ADD CONSTRAINT `aprr_admin_id` FOREIGN KEY (`aprr_admin_id`) REFERENCES `tbl_admin`(`admin_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------tbl_admin_permissions-------------------------------
ALTER TABLE `tbl_admin_permissions` ADD CONSTRAINT `admperm_admin_id` FOREIGN KEY (`admperm_admin_id`) REFERENCES `tbl_admin`(`admin_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------tbl_ads_batches--------------------------
ALTER TABLE `tbl_ads_batches` ADD CONSTRAINT `adsbatch_user_id` FOREIGN KEY (`adsbatch_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_ads_batches` ADD CONSTRAINT `adsbatch_target_country_id` FOREIGN KEY (`adsbatch_target_country_id`) REFERENCES `tbl_countries`(`country_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------tbl_ads_batch_products-------------------
ALTER TABLE `tbl_ads_batch_products` ADD CONSTRAINT `abprod_selprod_id` FOREIGN KEY (`abprod_selprod_id`) REFERENCES `tbl_seller_products`(`selprod_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------tbl_affiliate_commission_settings-------------------
ALTER TABLE `tbl_affiliate_commission_settings` ADD CONSTRAINT `afcommsetting_prodcat_id` FOREIGN KEY (`afcommsetting_prodcat_id`) REFERENCES `tbl_product_categories`(`prodcat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_affiliate_commission_settings` ADD CONSTRAINT `afcommsetting_user_id` FOREIGN KEY (`afcommsetting_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------tbl_affiliate_commission_setting_history-------------------
ALTER TABLE `tbl_affiliate_commission_setting_history` ADD CONSTRAINT `acsh_afcommsetting_id` FOREIGN KEY (`acsh_afcommsetting_id`) REFERENCES `tbl_affiliate_commission_settings`(`afcommsetting_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_affiliate_commission_setting_history` ADD CONSTRAINT `acsh_afcommsetting_prodcat_id` FOREIGN KEY (`acsh_afcommsetting_prodcat_id`) REFERENCES `tbl_product_categories`(`prodcat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_affiliate_commission_setting_history` ADD CONSTRAINT `acsh_afcommsetting_user_id` FOREIGN KEY (`acsh_afcommsetting_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------tbl_attribute_group_attributes-------------
ALTER TABLE `tbl_attribute_group_attributes` ADD CONSTRAINT `attr_attrgrp_id` FOREIGN KEY (`attr_attrgrp_id`) REFERENCES `tbl_attribute_groups`(`attrgrp_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ------------------tbl_banners------------------------
ALTER TABLE `tbl_banners` ADD CONSTRAINT `banner_blocation_id` FOREIGN KEY (`banner_blocation_id`) REFERENCES `tbl_banner_locations`(`blocation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ------------------tbl_banners_clicks------------------------
ALTER TABLE `tbl_banners_clicks` ADD CONSTRAINT `bclick_banner_id` FOREIGN KEY (`bclick_banner_id`) REFERENCES `tbl_banners`(`banner_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_banners_clicks` ADD CONSTRAINT `bclick_user_id` FOREIGN KEY (`bclick_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------tbl_banners_logs---------------------
ALTER TABLE `tbl_banners_logs` ADD CONSTRAINT `lbanner_banner_id` FOREIGN KEY (`lbanner_banner_id`) REFERENCES `tbl_banners`(`banner_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------tbl_banner_locations----------------
ALTER TABLE `tbl_banner_locations` ADD CONSTRAINT `blocation_collection_id` FOREIGN KEY (`blocation_collection_id`) REFERENCES `tbl_collections`(`collection_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------tbl_banner_location_dimensions-------------
ALTER TABLE `tbl_banner_location_dimensions` ADD CONSTRAINT `bldimension_blocation_id` FOREIGN KEY (`bldimension_blocation_id`) REFERENCES `tbl_banner_locations`(`blocation_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------------tbl_blog_contributions--------------------------
ALTER TABLE `tbl_blog_contributions` ADD CONSTRAINT `bcontributions_user_id` FOREIGN KEY (`bcontributions_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ------------------tbl_blog_post_comments-----------------------
ALTER TABLE `tbl_blog_post_comments` ADD CONSTRAINT `bpcomment_post_id` FOREIGN KEY (`bpcomment_post_id`) REFERENCES `tbl_blog_post`(`post_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_blog_post_comments` ADD CONSTRAINT `bpcomment_user_id` FOREIGN KEY (`bpcomment_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------tbl_blog_post_to_category---------------------------
ALTER TABLE `tbl_blog_post_to_category` ADD CONSTRAINT `ptc_bpcategory_id` FOREIGN KEY (`ptc_bpcategory_id`) REFERENCES `tbl_blog_post_categories`(`bpcategory_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_blog_post_to_category` ADD CONSTRAINT `ptc_post_id` FOREIGN KEY (`ptc_post_id`) REFERENCES `tbl_blog_post`(`post_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------------tbl_brands---------------------------
ALTER TABLE `tbl_brands` ADD CONSTRAINT `brand_seller_id` FOREIGN KEY (`brand_seller_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ------------------tbl_catalog_request_messages-------------
ALTER TABLE `tbl_catalog_request_messages` ADD CONSTRAINT `scatrequestmsg_scatrequest_id` FOREIGN KEY (`scatrequestmsg_scatrequest_id`) REFERENCES `tbl_seller_catalog_requests`(`scatrequest_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_catalog_request_messages` ADD CONSTRAINT `scatrequestmsg_from_user_id` FOREIGN KEY (`scatrequestmsg_from_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_catalog_request_messages` ADD CONSTRAINT `scatrequestmsg_from_admin_id` FOREIGN KEY (`scatrequestmsg_from_admin_id`) REFERENCES `tbl_admin`(`admin_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------------tbl_collection_to_records-----------------
ALTER TABLE `tbl_collection_to_records` ADD CONSTRAINT `ctr_collection_id` FOREIGN KEY (`ctr_collection_id`) REFERENCES `tbl_collections`(`collection_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------tbl_commission_settings-------------------
ALTER TABLE `tbl_commission_settings` ADD CONSTRAINT `commsetting_product_id` FOREIGN KEY (`commsetting_product_id`) REFERENCES `tbl_products`(`product_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_commission_settings` ADD CONSTRAINT `commsetting_user_id` FOREIGN KEY (`commsetting_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_commission_settings` ADD CONSTRAINT `commsetting_prodcat_id` FOREIGN KEY (`commsetting_prodcat_id`) REFERENCES `tbl_product_categories`(`prodcat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------tbl_commission_setting_history---------------------
ALTER TABLE `tbl_commission_setting_history` ADD CONSTRAINT `csh_commsetting_id` FOREIGN KEY (`csh_commsetting_id`) REFERENCES `tbl_commission_settings`(`commsetting_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_commission_setting_history` ADD CONSTRAINT `csh_commsetting_product_id` FOREIGN KEY (`csh_commsetting_product_id`) REFERENCES `tbl_products`(`product_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `tbl_commission_setting_history` ADD CONSTRAINT `csh_commsetting_user_id` FOREIGN KEY (`csh_commsetting_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_commission_setting_history` ADD CONSTRAINT `csh_commsetting_prodcat_id` FOREIGN KEY (`csh_commsetting_prodcat_id`) REFERENCES `tbl_product_categories`(`prodcat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------------tbl_content_block_to_category-----------------------------
ALTER TABLE `tbl_content_block_to_category` ADD CONSTRAINT `cbtc_prodcat_id` FOREIGN KEY (`cbtc_prodcat_id`) REFERENCES `tbl_product_categories`(`prodcat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_content_block_to_category` ADD CONSTRAINT `cbtc_cpage_id` FOREIGN KEY (`cbtc_cpage_id`) REFERENCES `tbl_content_pages`(`cpage_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------tbl_coupons_history----------------------------
ALTER TABLE `tbl_coupons_history` ADD CONSTRAINT `couponhistory_coupon_id` FOREIGN KEY (`couponhistory_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupons_history` ADD CONSTRAINT `couponhistory_order_id` FOREIGN KEY (`couponhistory_order_id`) REFERENCES `tbl_orders`(`order_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupons_history` ADD CONSTRAINT `couponhistory_user_id` FOREIGN KEY (`couponhistory_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------------tbl_coupons_hold-----------------------------
ALTER TABLE `tbl_coupons_hold` ADD CONSTRAINT `couponhold_coupon_id` FOREIGN KEY (`couponhold_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupons_hold` ADD CONSTRAINT `couponhold_user_id` FOREIGN KEY (`couponhold_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------------tbl_coupons_hold_pending_order-----------------
ALTER TABLE `tbl_coupons_hold_pending_order` ADD CONSTRAINT `ochold_order_id` FOREIGN KEY (`ochold_order_id`) REFERENCES `tbl_orders`(`order_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupons_hold_pending_order` ADD CONSTRAINT `ochold_coupon_id` FOREIGN KEY (`ochold_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------------tbl_coupon_to_brands--------------------------
ALTER TABLE `tbl_coupon_to_brands` ADD CONSTRAINT `ctb_brand_id` FOREIGN KEY (`ctb_brand_id`) REFERENCES `tbl_brands`(`brand_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_coupon_to_brands` ADD CONSTRAINT `ctb_coupon_id` FOREIGN KEY (`ctb_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------------tbl_coupon_to_category--------------------------
ALTER TABLE `tbl_coupon_to_category` ADD CONSTRAINT `ctc_prodcat_id` FOREIGN KEY (`ctc_prodcat_id`) REFERENCES `tbl_product_categories`(`prodcat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_coupon_to_category` ADD CONSTRAINT `ctc_coupon_id` FOREIGN KEY (`ctc_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------------------tbl_coupon_to_plan------------------------------
ALTER TABLE `tbl_coupon_to_plan` ADD CONSTRAINT `ctplan_spplan_id` FOREIGN KEY (`ctplan_spplan_id`) REFERENCES `tbl_seller_packages_plan`(`spplan_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_coupon_to_plan` ADD CONSTRAINT `ctplan_coupon_id` FOREIGN KEY (`ctplan_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------------tbl_coupon_to_products-------------------
ALTER TABLE `tbl_coupon_to_products` ADD CONSTRAINT `ctp_product_id` FOREIGN KEY (`ctp_product_id`) REFERENCES `tbl_products`(`product_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupon_to_products` ADD CONSTRAINT `ctp_coupon_id` FOREIGN KEY (`ctp_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------tbl_coupon_to_seller-----------------------------
ALTER TABLE `tbl_coupon_to_seller` ADD CONSTRAINT `cts_user_id` FOREIGN KEY (`cts_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupon_to_seller` ADD CONSTRAINT `cts_coupon_id` FOREIGN KEY (`cts_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------tbl_coupon_to_shops-----------------------------
/* ALTER TABLE `tbl_coupon_to_shops` ADD CONSTRAINT `cts_shop_id` FOREIGN KEY (`cts_shop_id`) REFERENCES `tbl_shops`(`shop_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupon_to_shops` ADD CONSTRAINT `cts_coupon_id` FOREIGN KEY (`cts_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; */

-- -------------------tbl_coupon_to_users-----------------------------
ALTER TABLE `tbl_coupon_to_users` ADD CONSTRAINT `ctu_user_id` FOREIGN KEY (`ctu_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_coupon_to_users` ADD CONSTRAINT `ctu_coupon_id` FOREIGN KEY (`ctu_coupon_id`) REFERENCES `tbl_coupons`(`coupon_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------------tbl_cron_log----------------
ALTER TABLE `tbl_cron_log` ADD CONSTRAINT `cronlog_cron_id` FOREIGN KEY (`cronlog_cron_id`) REFERENCES `tbl_cron_schedules`(`cron_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------------tbl_extra_attributes----------------------------
ALTER TABLE `tbl_extra_attributes` ADD CONSTRAINT `eattribute_eattrgroup_id` FOREIGN KEY (`eattribute_eattrgroup_id`) REFERENCES `tbl_extra_attribute_groups`(`eattrgroup_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------tbl_extra_attribute_groups----------------------
ALTER TABLE `tbl_extra_attribute_groups` ADD CONSTRAINT `eattrgroup_seller_id` FOREIGN KEY (`eattrgroup_seller_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------------tbl_faqs---------------------
ALTER TABLE `tbl_faqs` ADD CONSTRAINT `faq_faqcat_id` FOREIGN KEY (`faq_faqcat_id`) REFERENCES `tbl_faqs`(`faq_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------tbl_filters------------------------------------
ALTER TABLE `tbl_filters` ADD CONSTRAINT `filter_filtergroup_id` FOREIGN KEY (`filter_filtergroup_id`) REFERENCES `tbl_filter_groups`(`filtergroup_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------tbl_import_export_settings--------------------------------
ALTER TABLE `tbl_import_export_settings` ADD CONSTRAINT `impexp_setting_user_id` FOREIGN KEY (`impexp_setting_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------------tbl_shipping_durations-------------------------
ALTER TABLE `tbl_manual_shipping_api` ADD CONSTRAINT `mshipapi_sduration_id` FOREIGN KEY (`mshipapi_sduration_id`) REFERENCES `tbl_shipping_durations`(`sduration_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_manual_shipping_api` ADD CONSTRAINT `mshipapi_state_id` FOREIGN KEY (`mshipapi_state_id`) REFERENCES `tbl_states`(`state_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_manual_shipping_api` ADD CONSTRAINT `mshipapi_country_id` FOREIGN KEY (`mshipapi_country_id`) REFERENCES `tbl_countries`(`country_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------tbl_navigation_links--------------------
ALTER TABLE `tbl_navigation_links` ADD CONSTRAINT `nlink_nav_id` FOREIGN KEY (`nlink_nav_id`) REFERENCES `tbl_navigations`(`nav_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_navigation_links` ADD CONSTRAINT `nlink_cpage_id` FOREIGN KEY (`nlink_cpage_id`) REFERENCES `tbl_content_pages`(`cpage_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_navigation_links` ADD CONSTRAINT `nlink_category_id` FOREIGN KEY (`nlink_category_id`) REFERENCES `tbl_product_categories`(`prodcat_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------------tbl_notifications---------------
ALTER TABLE `tbl_notifications` ADD CONSTRAINT `notification_user_id` FOREIGN KEY (`notification_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ------------------tbl_options----------------
ALTER TABLE `tbl_options` ADD CONSTRAINT `option_seller_id` FOREIGN KEY (`option_seller_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------tbl_option_values----------------------------
ALTER TABLE `tbl_option_values` ADD CONSTRAINT `optionvalue_option_id` FOREIGN KEY (`optionvalue_option_id`) REFERENCES `tbl_options`(`option_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------------tbl_orders----------------------------------
ALTER TABLE `tbl_orders` ADD  CONSTRAINT `order_user_id` FOREIGN KEY (`order_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_orders` ADD  CONSTRAINT `order_language_id` FOREIGN KEY (`order_language_id`) REFERENCES `tbl_languages`(`language_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_orders` ADD  CONSTRAINT `order_currency_id` FOREIGN KEY (`order_currency_id`) REFERENCES `tbl_currency`(`currency_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_orders` ADD  CONSTRAINT `order_shippingapi_id` FOREIGN KEY (`order_shippingapi_id`) REFERENCES `tbl_shipping_apis`(`shippingapi_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_orders` ADD  CONSTRAINT `order_pmethod_id` FOREIGN KEY (`order_pmethod_id`) REFERENCES `tbl_payment_methods`(`pmethod_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_orders` ADD  CONSTRAINT `order_referrer_user_id` FOREIGN KEY (`order_referrer_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_orders` ADD  CONSTRAINT `order_affiliate_user_id` FOREIGN KEY (`order_affiliate_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ------------------tbl_orders_status_history-----------------------
ALTER TABLE `tbl_orders_status_history` ADD CONSTRAINT `oshistory_order_id` FOREIGN KEY (`oshistory_order_id`) REFERENCES `tbl_orders`(`order_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_orders_status_history` ADD CONSTRAINT `oshistory_op_id` FOREIGN KEY (`oshistory_op_id`) REFERENCES `tbl_order_products`(`op_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_orders_status_history` ADD CONSTRAINT `oshistory_orderstatus_id` FOREIGN KEY (`oshistory_orderstatus_id`) REFERENCES `tbl_orders_status`(`orderstatus_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------tbl_order_cancel_requests----------
ALTER TABLE `tbl_order_cancel_requests` ADD CONSTRAINT `ocrequest_user_id` FOREIGN KEY (`ocrequest_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_order_cancel_requests` ADD CONSTRAINT `ocrequest_op_id` FOREIGN KEY (`ocrequest_op_id`) REFERENCES `tbl_order_products`(`op_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `tbl_order_cancel_requests` ADD CONSTRAINT `ocrequest_ocreason_id` FOREIGN KEY (`ocrequest_ocreason_id`) REFERENCES `tbl_order_cancel_reasons`(`ocreason_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- --------------tbl_order_payments--------------
ALTER TABLE `tbl_order_payments` ADD CONSTRAINT `opayment_order_id` FOREIGN KEY (`opayment_order_id`) REFERENCES `tbl_orders`(`order_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -----------------------tbl_order_products--------------------
ALTER TABLE `tbl_order_products` ADD CONSTRAINT `op_order_id` FOREIGN KEY (`op_order_id`) REFERENCES `tbl_orders`(`order_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_order_products` ADD CONSTRAINT `op_selprod_id` FOREIGN KEY (`op_selprod_id`) REFERENCES `tbl_seller_products`(`selprod_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_order_products` ADD CONSTRAINT `op_selprod_user_id` FOREIGN KEY (`op_selprod_user_id`) REFERENCES `tbl_users`(`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_order_products` ADD CONSTRAINT `op_shop_id` FOREIGN KEY (`op_shop_id`) REFERENCES `tbl_shops`(`shop_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `tbl_order_products` ADD CONSTRAINT `op_status_id` FOREIGN KEY (`op_status_id`) REFERENCES `tbl_orders_status`(`orderstatus_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------------------tbl_order_product_charges-----------------
ALTER TABLE `tbl_order_product_charges` ADD CONSTRAINT `opcharge_op_id` FOREIGN KEY (`opcharge_op_id`) REFERENCES `tbl_order_products`(`op_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------------tbl_order_product_digital_download_links-------------------------
ALTER TABLE `tbl_order_product_digital_download_links` ADD CONSTRAINT `opddl_op_id` FOREIGN KEY (`opddl_op_id`) REFERENCES `tbl_order_products`(`op_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ---------------------------tbl_order_product_settings-----------------------------
ALTER TABLE `tbl_order_product_settings` ADD CONSTRAINT `opsetting_op_id` FOREIGN KEY (`opsetting_op_id`) REFERENCES `tbl_order_products`(`op_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------------tbl_order_product_shipment-------------------------
ALTER TABLE `tbl_order_product_shipment` ADD CONSTRAINT `opship_op_id` FOREIGN KEY (`opship_op_id`) REFERENCES `tbl_order_products`(`op_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- -------------------------tbl_order_product_shipping-------------------------
ALTER TABLE `tbl_order_product_shipment` ADD CONSTRAINT `opshipping_op_id` FOREIGN KEY (`opshipping_op_id`) REFERENCES `tbl_order_products`(`op_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
