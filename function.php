<?php
// wp_enqueue_script('custom-js', get_template_directory_uri() . '/js/custom.js', array(), time(), true);
// wp_enqueue_style('custom-css', get_template_directory_uri() . '/css/custom.css', array(), time());
function enable_cart_fragments() {
    if (is_checkout()) {
        wp_enqueue_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 'manage_cart_fragments');
function manage_cart_fragments() {
    if (is_checkout() || is_cart()) {
        wp_enqueue_script('wc-cart-fragments');
    } else {
        wp_dequeue_script('wc-cart-fragments');
    }
}


add_filter('woocommerce_checkout_fields', 'custom_checkout_fields');
function custom_checkout_fields($fields) {
    $fields['billing']['billing_region'] = array(
        'type' => 'text',   
        'label' => 'المنطقة',
        'placeholder' => 'أدخل اسم المنطقة',
        'required' => true, // جعل الحقل مطلوبًا
        'class' => array('form-row-wide'),
        'priority' => 20,
    );
    return $fields;
}
add_action('woocommerce_checkout_update_order_meta', 'save_region_to_order');
function save_region_to_order($order_id) {
    if (!empty($_POST['billing_region'])) {
        update_post_meta($order_id, '_billing_region', sanitize_text_field($_POST['billing_region']));
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'display_region_in_admin_order_meta', 10, 1);
function display_region_in_admin_order_meta($order) {
    $region = get_post_meta($order->get_id(), '_billing_region', true);
    if ($region) {
        echo '<p><strong>المنطقة:</strong> ' . esc_html($region) . '</p>';
    }
}
add_filter('woocommerce_checkout_fields', 'add_branch_to_checkout_fields');
function add_branch_to_checkout_fields($fields) {
    $fields['billing']['billing_branch'] = array(
        'type' => 'text',
        'label' => 'الفرع',
        'placeholder' => 'أدخل اسم الفرع',
        'required' => true, // جعل الحقل مطلوبًا
        'class' => array('form-row-wide'),
        'priority' => 25,
    );
    return $fields;
}
add_action('woocommerce_checkout_update_order_meta', 'save_branch_to_order');
function save_branch_to_order($order_id) {
    if (!empty($_POST['billing_branch'])) {
        update_post_meta($order_id, '_billing_branch', sanitize_text_field($_POST['billing_branch']));
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'display_branch_in_admin_order_meta', 10, 1);
function display_branch_in_admin_order_meta($order) {
    $branch = get_post_meta($order->get_id(), '_billing_branch', true);
    if ($branch) {
        echo '<p><strong>الفرع:</strong> ' . esc_html($branch) . '</p>';
    }
}
add_filter('manage_edit-shop_order_columns', 'add_branch_column_to_order_list');
function add_branch_column_to_order_list($columns) {
    $columns['billing_branch'] = 'الفرع';
    return $columns;
}

add_action('manage_shop_order_posts_custom_column', 'display_branch_in_order_list', 10, 2);
function display_branch_in_order_list($column, $post_id) {
    if ('billing_branch' === $column) {
        $branch = get_post_meta($post_id, '_billing_branch', true);
        echo esc_html($branch ? $branch : 'غير محدد');
    }
}

// وظيفة لتحديث حالة السلة
add_action('wp_ajax_check_cart_status', 'check_cart_status');
add_action('wp_ajax_nopriv_check_cart_status', 'check_cart_status');

function check_cart_status() {
    $cart_items_count = WC()->cart->get_cart_contents_count();

    if ($cart_items_count > 0) {
        wp_send_json_success(['has_items' => true]);
    } else {
        wp_send_json_success(['has_items' => false]);
    }

    wp_die();
}

add_filter('woocommerce_checkout_fields', 'customize_checkout_fields');
function customize_checkout_fields($fields) {
    // إزالة الحقول غير المطلوبة
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['shipping']); // إزالة قسم الشحن

    // تخصيص الحقول المطلوبة فقط
    $fields['billing']['billing_first_name']['required'] = true;
    $fields['billing']['billing_last_name']['required'] = true;
    $fields['billing']['billing_email']['required'] = true;
    $fields['billing']['billing_phone']['required'] = true;

    // إضافة حقل القسيمة الشرائية
    $fields['billing']['billing_coupon_code'] = array(
        'type' => 'text',
        'label' => 'هل لديك قسيمة شرائية؟',
        'placeholder' => 'أدخل رمز القسيمة هنا',
        'required' => false,
        'class' => array('form-row-wide'),
        'priority' => 25,
    );

    return $fields;
}
add_filter('woocommerce_enable_order_notes_field', '__return_false');
add_action('woocommerce_checkout_before_order_review', 'remove_order_review', 1);
function remove_order_review() {
    remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
}
add_filter('woocommerce_no_available_payment_methods_message', '__return_empty_string');

// Add SweetAlert2 Library
function enqueue_sweetalert2_library() {
    wp_enqueue_style('sweetalert2-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', array(), null);
    wp_enqueue_script('sweetalert2-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_sweetalert2_library');
function enqueue_custom_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');


// Multistage Select Script and css
function my_custom_inline_styles_scripts() {
    ?>
    <style>

        .step-indicator {
          display: flex;
          align-items: center;
          justify-content: space-between;
        }
        .step-icon {
          font-size: 3rem;
          color: #0c0a09;
        }
        
        .step-title {
          font-weight: bold;
          margin-top: 10px;
          font-size: 1.1rem;
        }
        
        .step-content {
          display: none;
        }
        
        .step-content.active {
          display: block;
        }
        
        .step-indicator {
          text-align: center;
          margin-bottom: 30px;
        }
        
        .step {
          display: inline-block;
          width: 22%;
          text-align: center;
        }
        
        .step.active .step-icon {
          color: #4dc1ec;
        }
        
        .hd-sub-text {
          font-size: 35px;
          color: #4dc1ec;
          font-weight: 800;
        }
        .btn-group {
          margin-top: 20px;
          gap: 15px;
          width: 100%;
          justify-content: start;
          
        }
        .custom-btn {
          width: 100px;
          height: 50px;
          border-radius: 25px;
          font-size: 1rem;
          font-weight: bold;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 5px;
          border: 1px solid #4dc1ec;
          background-color: #4dc1ec;
          color: white;
        }
        .custom-btn:hover {
          background-color: #1c1917;
          border: 1px solid #1c1917;
          transition: all ease-in-out 0.4s;
        }
        .d-none{
            display: none;
        }
        @media (max-width: 768px) {
          .hd-sub-text {
            font-size: 25px;
          }
          .step-title {
            font-size: 11px;
          }
          .step {
            width: 23%;
            margin-bottom: 15px;
          }
        
          .btn-group .btn {
            margin-bottom: 10px;
          }
        }


    </style>
   <script>
   document.addEventListener('DOMContentLoaded', function () {
    let currentStep = localStorage.getItem('currentStep') ? parseInt(localStorage.getItem('currentStep')) : 1;
    const totalSteps = 4;
    
   
    function getUserCarSelection() {
        return JSON.parse(localStorage.getItem('userSelection')) || {}; 
    }   
    function isProductCardSelected() {
        return document.querySelector('.product-card.selected') !== null;
    }
     function getSelectedTime() {
        return localStorage.getItem('selectedTime');
    }

    function showStep(step) {
        document.querySelectorAll(".step-content").forEach((content) => {
            content.classList.remove("active");
        });
        document.querySelector(`[data-step="${step}"]`).classList.add("active");

        document.querySelectorAll(".step").forEach((stepEl, index) => {
            stepEl.classList.toggle("active", index + 1 === step);
        });

        localStorage.setItem('currentStep', step);
        toggleButtons(); 
    }

    function toggleButtons() {
        const nextBtn = document.getElementById("nextBtn");
        const prevBtn = document.getElementById("prevBtn");
        const userCarSelection = getUserCarSelection();
        
        if (currentStep === 1) {
            prevBtn.classList.add("d-none");
        } else {
            prevBtn.classList.remove("d-none");
        }

        if (currentStep === totalSteps) {
            nextBtn.classList.add("d-none");
            
        } else {
            nextBtn.classList.remove("d-none");
        }
    }

   
    function showSweetAlert(message) {
        Swal.fire({
            title: 'الرجاء الاختيار',
            text: message,
            icon: 'warning',
            confirmButtonText: 'موافق',
            confirmButtonColor: '#4dc1ec'
        });
    }

    
    showStep(currentStep);

    
    document.getElementById("nextBtn").addEventListener("click", function () {
        const userCarSelection = getUserCarSelection();
        console.log(userCarSelection)

        // Step 1: Check if carSize is selected
        if (currentStep === 1 && !userCarSelection.carSize) {
            showSweetAlert('يرجى اختيار حجم السيارة');
            return; 
        }

        // Step 2: Check if category is selected
         if (currentStep === 2) {
            if (!userCarSelection.category || !userCarSelection) {
                showSweetAlert('يرجى اختيار الفئة');
                return; 
            }
            if (!isProductCardSelected()) {
                showSweetAlert('يرجى اختيار خدمة  ');
                return; 
            }
        }
        
        if (currentStep === 3) {
            const selectedTime = getSelectedTime();
            if (!selectedTime) {
                showSweetAlert('يرجى اختيار اسم الفرع و التاريخ و الوقت');
                return; 
            }
        }

        if (currentStep < totalSteps) {
            showStep(++currentStep);
        }
    });

    document.getElementById("prevBtn").addEventListener("click", function () {
        if (currentStep > 1) {
            showStep(--currentStep);
        }
    });
});

</script>

    <?php
}
add_action('wp_footer', 'my_custom_inline_styles_scripts');




// Shortcode to display car size and category selection
add_shortcode('car_size_and_category_selection', 'display_car_size_and_category_selection');
function display_car_size_and_category_selection() {
    ob_start();
    ?>
     <section class="ltb-section ltb-step-01 ltb-section-bg-black ltb-bg-cover" id="ltb-step-01">
        <div class="container custom-container">
            <div id="hd-container" style="width:100%">
                <!--Indicator -->
                <div class="step-indicator">
                      <div class="step active">
                        <i class="fas fa-car step-icon"></i>
                        <div class="step-title">مقاس السيارة</div>
                      </div>
                      <div class="step">
                        <i class="fas fa-tools step-icon"></i>
                        <div class="step-title"> الخدمة المطلوبة</div>
                      </div>
                      <div class="step">
                        <i class="fas fa-map-marker-alt step-icon"></i>
                       <div class="step-title">  الوقت والفرع</div>
                      </div>
                      <div class="step">
                        <i class="fas fa-clipboard-check step-icon"></i>
                        <div class="step-title">بيانات الحجز</div>
                      </div>
                </div>
                 <form id="multiStepForm">
                    <div class="step-content active" data-step="1">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 20px; text-align: center; margin: 20px 0;">
                            <hr id="hd-hr-left" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                            <div id="hd-text-container">
                                <div id="hd-main-text" style="font-size: 24px; font-weight: bold; color: #000;">مقاس السيارة</div>
                                    <div id="hd-sub-text" style="font-size: 14px; color: #4dc1ec;">من فضلك قم باختيار حجم سيارتك</div>
                            </div>
                            <hr id="hd-hr-right" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                        </div>
                       <div id="ltb-car-size-input" class="car-size-container">
                            <input type="hidden" id="selected_car_size" name="selected_car_size" value="">
                            <button type="button" class="ltb-car-size-input-option" data-car-size="سيارة صغيرة" onclick="selectCarSize(this)">
        <img decoding="async" class="ltb-car-size-input-media" src="https://darkgoldenrod-cormorant-325396.hostingersite.com/wp-content/uploads/2025/01/1LARGE-3copy.webp">
        <span class="car-size-label"><strong>صغير</strong></span>
    </button>
                            <button type="button" class="ltb-car-size-input-option" data-car-size="سيارة وسط" onclick="selectCarSize(this)">
        <img decoding="async" class="ltb-car-size-input-media" src="https://darkgoldenrod-cormorant-325396.hostingersite.com/wp-content/uploads/2025/01/1LARGE-2copy.webp">
        <span class="car-size-label"><strong>وسط</strong></span>
    </button>
                            <button type="button" class="ltb-car-size-input-option" data-car-size="سيارة كبيرة" onclick="selectCarSize(this)">
        <img decoding="async" class="ltb-car-size-input-media" src="https://darkgoldenrod-cormorant-325396.hostingersite.com/wp-content/uploads/2025/01/1LARGE-copy-copy.webp">
        <span class="car-size-label"><strong>كبير</strong></span>
    </button>
                        </div>

                    </div>
                    </div>
                     <!--ختار الخدمة المطلوبة-->
                    <div class="step-content " data-step="2">
                        <div id="category-selection" class="category-container">
                            <div id="hd-container" style="width:100%">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 20px; text-align: center; margin: 20px 0;">
                                    <hr id="hd-hr-left" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                                    <div id="hd-text-container">
                                        <div id="hd-main-text" style="font-size: 24px; font-weight: bold; color: #000;">اختار الخدمة المطلوبة</div>
                                        <div id="hd-sub-text" style="font-size: 14px; color: #4dc1ec;">من فضلك قم باختيار الخدمة</div>
                                    </div>
                                    <hr id="hd-hr-right" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                                </div>                   
                            <div class="categories-wrapper">
                              <!-- حماية -->
<button class="category-btn" data-category="حماية" onclick="selectCategory(this)" type="button">
    <img src="https://dettaglioauto.sa/wp-content/uploads/2019/06/PPF-icon-3.svg" alt="حماية">
    <span>حماية</span>
</button>

<!-- عازل حراري نانوسيراميك -->
<button class="category-btn" data-category="عازل حراري نانوسيراميك" onclick="selectCategory(this)" type="button">
    <img src="https://dettaglioauto.sa/wp-content/uploads/2019/06/TPF-icon-3.svg" alt="عازل حراري نانوسيراميك">
    <span>عازل حراري نانوسيراميك</span>
</button>

<!-- نانو سيراميك -->
<button class="category-btn" data-category="نانو سيراميك" onclick="selectCategory(this)" type="button">
    <img src="https://dettaglioauto.sa/wp-content/uploads/2019/06/Nano-icon-3.svg" alt="نانو سيراميك">
    <span>نانو سيراميك</span>
</button>

<!-- تلميع -->
<button class="category-btn" data-category="تلميع" onclick="selectCategory(this)" type="button">
    <img src="https://dettaglioauto.sa/wp-content/uploads/2019/06/polish-icon-2.svg" alt="تلميع">
    <span>تلميع</span>
</button>

                            </div>
                        </div>
                        <div id="filtered-products" class="filtered-products" style="margin-top: 30px;">                
                            <div id="products-container"></div>
                        </div>
                    </div>
                    </div>
                    <!-- اختيار المنطقة والفرع -->
                    <div class="step-content " data-step="3">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 20px; text-align: center; margin: 20px 0;">
                            <hr id="hd-hr-left" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                            <div id="hd-text-container">
                                <div id="hd-main-text" style="font-size: 24px; font-weight: bold; color: #000;">قم باختيار الوقت والفرع</div>
                                <div id="hd-sub-text" style="font-size: 14px; color: #4dc1ec;">من فضلك قم باختيار الفرع</div>
                            </div>
                            <hr id="hd-hr-right" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                        </div>
                        <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px; width: 100%;">
                            <label for="region-select" style="color: black;">قم باختيار المنطقة</label>
                            <select id="region-select" onchange="updateBranches()" style="width: 200px; padding: 10px; margin-top: 5px;">
                                <option value="" disabled selected>أختار المنطقة</option>
                                <option value="الرياض">الرياض</option>
                                <option value="المدينة المنورة">المدينة المنورة</option>
                                <option value="المنطقة الشرقية">المنطقة الشرقية</option>
                                <option value="القصيم">القصيم</option>
                            </select>
                        </div>
                        <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px; width: 100%;">
                            <label for="branch-select" style="color: black;">قم باختيار الفرع</label>
                            <select id="branch-select" disabled style="width: 200px; padding: 10px; margin-top: 5px;">
                                <option value="" disabled selected>أختر الفرع</option>
                            </select>
                        </div>            
                        <div id="calendar-time-container" style="display: none; justify-content: center; gap: 50px; margin-top: 30px;">
                            <!-- التقويم -->
                            <div id="calendar-container" style="text-align: center;">
                                <h4 style="color: black;">التاريخ</h4>
                                <div id="datepicker"></div>
                            </div>
                            <div id="time-container" style="text-align: center;">
                                <h4 style="color: white;">الوقت</h4>
                                <div id="am-time-buttons" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 15px;">
    <button class="time-btn" data-time="9:00 AM" type="button">AM 9:00</button>
    <button class="time-btn" data-time="10:00 AM" type="button">AM 10:00</button>
    <button class="time-btn" data-time="11:00 AM" type="button">AM 11:00</button>
</div>
<div id="pm-time-buttons" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
    <button class="time-btn" data-time="4:00 PM" type="button">PM 4:00</button>
    <button class="time-btn" data-time="5:00 PM" type="button">PM 5:00</button>
    <button class="time-btn" data-time="6:00 PM" type="button">PM 6:00</button>
    <button class="time-btn" data-time="7:00 PM" type="button">PM 7:00</button>
    <button class="time-btn" data-time="8:00 PM" type="button">PM 8:00</button>
</div>

                            </div>
                        </div>
                    </div>
                    <div class="step-content " data-step="4">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 20px; text-align: center; margin: 20px 0;">
                                <hr id="hd-hr-left" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                                <div id="hd-text-container">
                                    <div id="hd-main-text" style="font-size: 24px; font-weight: bold; color: #000;">بيانات الحجز</div>
                                    <div id="hd-sub-text" style="font-size: 14px; color: #4dc1ec;">من فضلك قم بتعبئة معلوماتك لتأكيد الحجز</div>
                                </div>
                                <hr id="hd-hr-right" style="flex: 1; border: 1px solid #4dc1ec; margin: 0;">
                        </div>
                        <?php
                        // عرض نموذج الدفع الخاص بـ WooCommerce
                        echo do_shortcode('[woocommerce_checkout]');
                        ?>
                    </div> 
                    <div class="btn-group mt-4">
                            <button type="button" class="custom-btn" id="prevBtn">
                            <i class="fa-solid fa-chevron-right ml-2"></i> السابق
                            </button>
                            <button type="button" class="custom-btn" id="nextBtn">
                            التالي <i class="fa-solid fa-chevron-left mr-2"></i>
                            </button>
                    </div>
                </form>
                
            </div>
        </div>
    </section>

    <style>
   .time-btn {
    background-color: transparent;
    border: 2px solid #4DC1EC;
    color: #4DC1EC;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    text-transform: uppercase;
}

.time-btn:hover {
    background-color: #4DC1EC;
    color: white;
}

.time-btn.selected-time {
    background-color: #4DC1EC; /* اللون الأخضر عند التحديد */
    color: white; /* لون النص عند التحديد */
    border-color: #00ff00; /* لون الحدود */
}






   #hd-container {
    text-align: center;
    margin-bottom: 20px;
    position: relative;
}

/* الخطوط على الجانبين */
#hd-hr-left, #hd-hr-right {
    border: 1px solid #4dc1ec; /* استبدال الأحمر باللبني */
    width: 20%;
    display: inline-block;
    margin: 0 10px;
    vertical-align: middle;
}

/* النص الرئيسي */
#hd-container h1 {
    font-size: 24px;
    font-weight: bold;
    color: #000; /* استبدال الأبيض بالأسود */
    margin: 0;
}

/* النص الفرعي */
#hd-container p {
    font-size: 14px;
    color: #4dc1ec; /* استبدال الأحمر باللبني للنص الفرعي */
    margin-top: 5px;
}

/* النص الرئيسي */
#hd-text-container {
    display: inline-block;
    text-align: center;
    margin-top:10px ;
}

#hd-main-text {
    font-size: 24px;
    font-weight: bold;
    color: #000;
}

#hd-sub-text {
    font-size: 14px;
    color: #bbb;
}

/* الحاوية الرئيسية لمقاسات السيارة */
.car-size-container {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

.ltb-car-size-input-option {
    width: 150px;
    border: 2px solid transparent;
    border-radius: 10px;
    background-color: #222;
    text-align: center;
    padding: 10px;
    cursor: pointer;
    transition: border-color 0.3s ease, transform 0.3s ease;
}

.ltb-car-size-input-option:hover {
    border-color: #00ff00;
    transform: scale(1.05);
}

.ltb-car-size-input-media {
    width: 100%;
    border-radius: 10px;
    margin-bottom: 10px;
}

.car-size-label {
    font-size: 16px;
    font-weight: bold;
    color: #fff;
}

    #calendar-time-container {
    display: none;
}
/* جعل الأعمدة Full Width */
#customer_details {
    display: block; /* إزالة الأعمدة */
    width: 100%;
}

#customer_details .col-1, 
#customer_details .col-2 {
    width: 100%; /* اجعل كل قسم يأخذ العرض بالكامل */
    margin: 0 auto;
}

.woocommerce-billing-fields {
    max-width: 700px; /* ضبط عرض القسم */
    margin: 0 auto; /* توسيط القسم */
    padding: 20px;
    background-color: #4DC1EC; /* لون الخلفية */
    border-radius: 10px; /* جعل الحواف مستديرة */
}

.woocommerce-billing-fields__field-wrapper p {
    display: block; /* جعل الحقول في سطر واحد */
    width: 100%;
    margin-bottom: 15px;
}

.woocommerce-billing-fields label {
    color: white; /* تغيير لون النص */
}

.woocommerce-billing-fields input {
    width: 100%; /* جعل الحقول تمتد بالكامل */
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
}
/* إزالة تأثير الأعمدة */
.woocommerce #customer_details .col-1,
.woocommerce #customer_details .col-2 {
    float: none;
    margin: 0;
    padding: 0;
}

#region-select, #branch-select {
    background-color: #222;
    color: white;
    border: 1px solid #444;
    border-radius: 5px;
    font-size: 14px;
}

#datepicker {
    background-color: #4DC1EC;
    border-radius: 5px;
    padding: 10px;
    color: white;
}

#checkout-section {
    display: none; /* مخفي بشكل افتراضي */
}
.container.custom-container {
    width: 100%; /* العرض بالكامل */
    margin: 0; /* إزالة الهوامش */
    padding: 0 15px; /* ترك مساحة داخلية بسيطة */
}
.category-container {
    display: flex;
    flex-direction: column;
    align-items: center;
        width: 100%;
margin-block: 20px;
}

.categories-wrapper {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* تقسيم إلى 4 أعمدة */
    gap: 20px; /* مسافة بين العناصر */
    width: 100%; /* جعل العرض كاملًا */
}

.category-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #fff;
    border: none;
    padding: 10px;
    cursor: pointer;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.category-btn img {
    width: 60px;
    height: auto;
    margin-bottom: 10px;
}

.category-btn span {
    font-size: 18px; /* حجم النص */
    color: #fff; /* لون النص أبيض */
    text-align: center; /* توسيط النص */
    font-weight: bold; /* النص بخط عريض */
}


.ltb-section {
  background-color: #fff; /* خلفية بيضاء */
  color: #000; /* لون النص أسود افتراضي */
  padding: 20px 0; /* مسافات حول النص */
  text-align: center; /* النص في المنتصف أفقياً */
  width: 100%; /* العرض كامل */
  font-weight: bold; /* النص بخط عريض */
}

        .car-size-container, { display: flex; justify-content: center; gap: 15px; margin-top: 20px; }
.ltb-car-size-input-option {
    width: 100%; /* العرض بالكامل */
    padding: 10px;
    background-color: #222;
    color: white;
    text-align: center;
    border-radius: 10px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}        .ltb-car-size-input-option:hover {
            background-color: #444;
        }
        .ltb-car-size-input-option.selected {
            border: 2px solid #4DC1EC;
            background-color: #333;
        }
.category-btn {
    width: 100%; /* العرض بالكامل */
    padding: 15px;
    background-color: #484848; /* اللون الرمادي الداكن للأزرار غير المختارة */
    color: white; /* النص باللون الأبيض */
    border: none;
    border-radius: 5px;
    margin-bottom: 10px;
    transition: background-color 0.3s ease, transform 0.2s ease; /* تأثير سلس للتغيير */
    font-size: 16px; /* حجم النص */
    font-weight: bold; /* النص بخط عريض */
    text-align: center; /* توسيط النص */
    cursor: pointer;
}

.category-btn:hover {
    transform: scale(1.02); /* تأثير تكبير طفيف عند التمرير */
}

.category-btn.selected {
    background-color: #4DC1EC; /* اللون اللبني للأزرار المختارة */
    color: white; /* النص يظل أبيض */
    transform: scale(1.05); /* تأثير تكبير إضافي عند الاختيار */
}

    #filtered-products {
    margin-top: 0;
    box-sizing: border-box; /* لضمان عدم تجاوز العناصر حدود الـ container */
}

#products-container {
    display: flex; /* استخدام Flexbox لجعل العناصر تصطف أفقياً */
    flex-wrap: wrap; /* السماح بالتفاف العناصر إذا ضاق العرض */
    gap: 20px; /* المسافة بين المنتجات */
    justify-content: space-between; /* توزيع العناصر بالتساوي */
    box-sizing: border-box; /* التأكد من تضمين الحواف */
}

.product-card {
    flex: 1 1 calc(33.333% - 20px); /* عرض كل بطاقة 33.33% من العرض المتاح مع مراعاة الفراغات */
    max-width: calc(33.333% - 20px); /* ضمان عدم تجاوز الحد الأقصى */
    background: #fff;
    border: 1px solid #ddd; /* إضافة حدود خفيفة */
    border-radius: 10px;
    overflow: hidden;
    padding: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* ظل خفيف */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: scale(1.03); /* تأثير تكبير طفيف عند التمرير */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}


.product-image img {
    width: 100%;
    height: auto;
    border-bottom: 1px solid #eee;
    margin-bottom: 10px; /* تقليل المسافة بين الصورة والمحتوى */
    border-radius: 5px; /* حواف ناعمة للصورة */
}

.product-info {
    padding: 10px 0; /* تقليل الحشو العمودي */
}

.product-title {
    font-size: 16px; /* حجم النص المناسب */
    font-weight: bold;
    margin-bottom: 5px; /* تقليل المسافة السفلية */
    color: #000; /* اللون الأسود للنصوص */
}

.product-price-container {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #000; /* خلفية سوداء */
    color: #fff; /* نص أبيض */
    font-weight: bold; /* النص بخط عريض */
    font-size: 16px; /* حجم النص */
    padding: 10px 20px; /* مسافات داخلية */
    border-radius: 10px; /* حواف مستديرة */
    width: fit-content; /* يجعل العرض يناسب المحتوى */
}

.product-price {
    font-size: 24px; /* حجم النص للسعر */
    margin-right: 5px; /* مسافة بين الرقم والعملة */
}

.currency {
    font-size: 16px; /* حجم النص للعملة */
    margin-right: 5px;
}

.increment {
    font-size: 20px; /* حجم النص للرمز + */
    margin-left: 5px; /* مسافة بين الرمز والباقي */
}


.in-cart {
    border: 2px solid #00ff00;
    background-color: #e3f4fc;
}

.installment {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    margin-bottom: 10px;
    color: #000; /* النص الأسود */
}

.installment img {
    width: 50px;
    margin-left: 5px;
}

.more-details {
    display: block;
    background-color: #4dc1ec;
    color: white;
    text-align: center;
    padding: 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.more-details:hover {
    background-color: #c00000;
}

/* استجابة للشاشات */
@media (max-width: 1024px) {
    #products-container {
        grid-template-columns: repeat(2, 1fr); /* صفين للمنتجات في الشاشات المتوسطة */
    }
}

@media (max-width: 768px) {
    #products-container {
        grid-template-columns: repeat(1, 1fr); /* منتج واحد في الشاشات الصغيرة */
    }
}


    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const regionSelect = document.getElementById('region-select');
        const branchSelect = document.getElementById('branch-select');

    // تحديث حقل "المنطقة" في Checkout عند تغيير القائمة المنسدلة للمنطقة
    regionSelect.addEventListener('change', function () {
        const selectedRegion = this.value;

        // العثور على حقل Checkout الخاص بـ "المنطقة" وتحديث قيمته
        const regionField = document.getElementById('billing_region');
        if (regionField) {
            regionField.value = selectedRegion; // تعيين القيمة
        } else {
            console.error('حقل المنطقة (billing_region) غير موجود في Checkout.');
        }
    });

    // تحديث حقل "الفرع" في Checkout عند تغيير القائمة المنسدلة للفرع
    branchSelect.addEventListener('change', function () {
        const selectedBranch = this.value;

        // العثور على حقل Checkout الخاص بـ "الفرع" وتحديث قيمته
        const branchField = document.getElementById('billing_branch');
        if (branchField) {
            branchField.value = selectedBranch; // تعيين القيمة
        } else {
            console.error('حقل الفرع (billing_branch) غير موجود في Checkout.');
        }
    });
});
    // Function to handle time button selection
    document.addEventListener('DOMContentLoaded', () => {
    const timeButtons = document.querySelectorAll('.time-btn');

    if (timeButtons.length > 0) {
        timeButtons.forEach(button => {
            button.addEventListener('click', function () {
                // إزالة الفئة "selected-time" من جميع الأزرار
                timeButtons.forEach(btn => btn.classList.remove('selected-time'));

                // إضافة الفئة "selected-time" إلى الزر المحدد
                this.classList.add('selected-time');

                // حفظ الوقت المحدد في localStorage
                const selectedTime = this.getAttribute('data-time');
                localStorage.setItem('selectedTime', selectedTime);

                // عرض رسالة تأكيد
                Swal.fire({
                    icon: 'success',
                    title: 'تم اختيار الوقت',
                    text: `الوقت: ${selectedTime}`
                });

                console.log('تم اختيار الوقت:', selectedTime); // تحقق من تشغيل الكود
            });
        });

        // استعادة الوقت المحدد عند تحميل الصفحة
        const savedTime = localStorage.getItem('selectedTime');
        if (savedTime) {
            const selectedButton = document.querySelector(`.time-btn[data-time="${savedTime}"]`);
            if (selectedButton) {
                selectedButton.classList.add('selected-time');
                console.log('تم استعادة الوقت:', savedTime);
            }
        }
    } else {
        console.warn('لا توجد أزرار متوفرة لتحديد الوقت.');
    }
});
    document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('region-select');
    const branchSelect = document.getElementById('branch-select');

    // تحديث حقول Checkout عندما تتغير القيمة في القائمة المنسدلة الخاصة بالمنطقة
    regionSelect.addEventListener('change', function () {
        const selectedRegion = this.value;

        // البحث عن حقل Checkout الخاص بالمنطقة وتحديثه
        const regionField = document.querySelector('input[name="billing_region"]');
        if (regionField) {
            regionField.value = selectedRegion; // تعيين القيمة
        }

        console.log('تم تحديث المنطقة في Checkout:', selectedRegion);
    });

    // تحديث حقول Checkout عندما تتغير القيمة في القائمة المنسدلة الخاصة بالفرع
    branchSelect.addEventListener('change', function () {
        const selectedBranch = this.value;

        // البحث عن حقل Checkout الخاص بالفرع وتحديثه
        const branchField = document.querySelector('input[name="billing_branch"]');
        if (branchField) {
            branchField.value = selectedBranch; // تعيين القيمة
        }

        console.log('تم تحديث الفرع في Checkout:', selectedBranch);
    });
});
    function showGuide() {
        document.getElementById('car-size-guide-modal').style.display = 'flex';
        document.getElementById('modal-view-1').style.display = 'block';
        document.getElementById('modal-view-2').style.display = 'none';
    }
    function showExamples() {
    document.getElementById('modal-view-1').style.display = 'none';
    document.getElementById('modal-view-2').style.display = 'block';
}
    function closeModal() {
        document.getElementById('car-size-guide-modal').style.display = 'none';
    }
    function toggleCart(productId, card) {
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        method: 'POST',
        data: {
            action: 'toggle_cart_item',
            product_id: productId
        },
        success: function (response) {
            if (response.success) {
                if (response.data.added) {
                    card.classList.add('selected');
                    card.classList.add('in-cart');
                    Swal.fire({
                        icon: 'success',
                        title: 'تمت الإضافة',
                        text: 'تمت إضافة المنتج إلى السلة!'
                    });
                } else {
                    card.classList.remove('selected');
                    card.classList.remove('in-cart');
                    Swal.fire({
                        icon: 'info',
                        title: 'تمت الإزالة',
                        text: 'تمت إزالة المنتج من السلة.'
                    });
                }

                // تحديث WooCommerce Fragments
                refreshWooCommerceFragments();
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'حدث خطأ أثناء إدارة السلة. حاول مرة أخرى.'
            });
        }
    });
}

    // حفظ خيارات المستخدم في localStorage
    function saveUserSelection(key, value) {
        let userSelection = JSON.parse(localStorage.getItem('userSelection')) || {};
        userSelection[key] = value;
        localStorage.setItem('userSelection', JSON.stringify(userSelection));
    }

    // استعادة خيارات المستخدم من localStorage
   function restoreUserSelection() {
    const savedSelection = JSON.parse(localStorage.getItem('userSelection'));
    if (savedSelection) {
        if (savedSelection.carSize) {
            selectedCarSize = savedSelection.carSize;
            document.querySelectorAll('.ltb-car-size-input-option').forEach(option => {
                if (option.getAttribute('data-car-size') === selectedCarSize) {
                    option.classList.add('selected');
                }
            });
        }

        if (savedSelection.category) {
            selectedCategory = savedSelection.category;
            document.querySelectorAll('.category-btn').forEach(btn => {
                if (btn.getAttribute('data-category') === selectedCategory) {
                    btn.classList.add('selected');
                }
            });
        }
    }
}

    
    document.addEventListener('DOMContentLoaded', function () {
    // استرجاع البيانات المحفوظة في LocalStorage
    let savedCarSize = localStorage.getItem('selectedCarSize');
    let savedCategory = localStorage.getItem('selectedCategory');

    // لو القيم موجودة، استدعاء المنتجات تلقائيًا بدون الحاجة لاختيار الفئة يدويًا
    if (savedCarSize && savedCategory) {
        selectedCarSize = savedCarSize;
        selectedCategory = savedCategory;

        // إضافة الكلاسات `selected` للعناصر المختارة
        document.querySelectorAll('.ltb-car-size-input-option').forEach(option => {
            if (option.getAttribute('data-car-size') === selectedCarSize) {
                option.classList.add('selected');
            }
        });

        document.querySelectorAll('.category-btn').forEach(btn => {
            if (btn.getAttribute('data-category') === selectedCategory) {
                btn.classList.add('selected');
            }
        });

        // تحميل المنتجات تلقائيًا
        fetchFilteredProducts();
    }
});

    function updateCheckoutVisibility() {
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        method: 'POST',
        data: { action: 'check_cart_status' },
        success: function (response) {
            const checkoutSection = document.getElementById('checkout-section');
            console.log('حالة السلة:', response); // للتحقق من الاستجابة

            if (response.success) {
                if (response.data.has_items) {
                    checkoutSection.style.display = 'block'; }
                // } else {
                //     checkoutSection.style.display = 'none'; // إخفاء Checkout
                //     // تم إزالة التنبيه الخاص بالسلة الفارغة
                // }
            }
        },
        error: function () {
            console.error('خطأ في التحقق من حالة السلة.');
        }
    });

}
    // تحديث حالة السلة
    function updateCheckoutSection() {
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        method: 'POST',
        data: {
            action: 'check_cart_status'
        },
        success: function (response) {
            if (response.success) {
                if (response.data.has_items) {
                    // عرض قسم Checkout
                    document.getElementById('checkout-section').style.display = 'block';
                } else {
                    // إخفاء قسم Checkout
                    document.getElementById('checkout-section').style.display = 'none';
                }
            }
        },
        error: function () {
            console.error('خطأ في التحقق من حالة السلة.');
        }
    });
}

    // عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function () {
    updateCheckoutVisibility(); // التحقق من حالة السلة عند التحميل
});

    // تحديث حالة السلة عند إضافة أو إزالة منتجات
    document.addEventListener('click', function (event) {
        if (event.target.matches('.product-card')) {
            setTimeout(function () {
                updateCheckoutSection();
            }, 500); // تأخير بسيط لضمان تنفيذ الطلب
        }
    });

    document.querySelectorAll('.time-btn').forEach(button => {
    button.addEventListener('click', function () {
        document.querySelectorAll('.time-btn').forEach(btn => btn.classList.remove('selected-time'));
        this.classList.add('selected-time');

        // إظهار رسالة تأكيد
        Swal.fire({
            icon: 'success',
            title: 'تم اختيار الوقت',
            text: `الوقت: ${this.dataset.time}`
        });
    });
});
 
    document.getElementById('branch-select').addEventListener('change', function () {
    if (this.value) {
        // أظهر قسم التقويم والوقت
        document.getElementById('calendar-time-container').style.display = 'flex';

        // تفعيل التقويم
        jQuery('#datepicker').datepicker({
            minDate: 0, // بدءًا من اليوم
            maxDate: "+1M", // حتى شهر من الآن
            beforeShowDay: function (date) {
                let day = date.getDay();
                // تعطيل يوم الجمعة
                return [day !== 5, ""];
            },
            onSelect: function (dateText) {
                // إظهار رسالة عند اختيار التاريخ
                Swal.fire({
                    icon: 'success',
                    title: 'تم اختيار التاريخ',
                    text: `التاريخ: ${dateText}`
                });
            }
        });
    }
});
    document.getElementById('branch-select').addEventListener('change', function () {
    if (this.value) {
        document.getElementById('calendar-container').style.display = 'block';
        document.getElementById('time-container').style.display = 'block';

        jQuery('#datepicker').datepicker({
            minDate: 0,
            maxDate: "+1M",
            beforeShowDay: function (date) {
                let day = date.getDay();
                return [day !== 5, ""];
            },
            onSelect: function (dateText) {
                Swal.fire({
                    icon: 'success',
                    title: 'تم اختيار التاريخ',
                    text: `التاريخ: ${dateText}`
                });
            }
        });
    }
});

    let branchesByRegion = {
    "الرياض": [
        "فرع حي النهضة - طريق خريص",
        "فرع حي الخالديه - شارع زيد بن حارثة \"قسم التلميع\"",
        "فرع حي الخالدية - شارع زيد بن حارثة \"قسم العازل والحماية\"",
        "فرع حي الصحافه - شارع أنس بن مالك",
        "فرع حي القيروان - طريق الملك فهد",
        "فرع حي ظهرة نمار - مخرج 27",
        "فرع حي النفل - طريق ابو بكر",
        "فرع حي المروج - طريق الامام سعود"
    ],
    "المدينة المنورة": [
        "فرع حي السلام - طريق الامام مسلم"
    ],
    "المنطقة الشرقية": [
        "فرع حي القشله - طريق الملك فهد"
    ],
    "القصيم": [
        "فرع حي الرحاب - طريق عمر بن الخطاب"
    ]
};

    function updateBranches() {
    let region = document.getElementById('region-select').value;
    let branchSelect = document.getElementById('branch-select');
    branchSelect.innerHTML = '<option value="" disabled selected>اختر الفرع</option>';

    if (region && branchesByRegion[region]) {
        branchesByRegion[region].forEach(branch => {
            let option = document.createElement('option');
            option.value = branch;
            option.textContent = branch;
            branchSelect.appendChild(option);
        });
        branchSelect.disabled = false;
    } else {
        branchSelect.disabled = true;
    }
}

    document.getElementById('branch-select').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('calendar-container').style.display = 'block';

        jQuery('#datepicker').datepicker({
            minDate: 0,
            maxDate: "+1M",
            beforeShowDay: function(date) {
                let day = date.getDay();
                // Disable Fridays
                return [day !== 5, ""];
            },
            onSelect: function(dateText) {
                Swal.fire({
                    icon: 'success',
                    title: 'تم اختيار التاريخ',
                    text: `التاريخ: ${dateText}`
                });
            }
        });
    }
});

    let selectedCarSize = '';
    let selectedCategory = '';

    function selectCarSize(element) {
            document.querySelectorAll('.ltb-car-size-input-option').forEach(option => option.classList.remove('selected'));
            element.classList.add('selected');
            selectedCarSize = element.getAttribute('data-car-size');
             saveUserSelection('carSize', selectedCarSize);
            console.log(selectedCarSize)
            // SweetAlert2 Message
            Swal.fire({
                icon: 'success',
                title: 'تم اختيار حجم السيارة',
                text: `حجم السيارة: ${selectedCarSize}`
            });

            fetchFilteredProducts();
        }
    function selectCategory(element) {
            document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('selected'));
            element.classList.add('selected');
            selectedCategory = element.getAttribute('data-category');
            console.log(selectedCategory);
            saveUserSelection('category', selectedCategory);

            // SweetAlert2 Message
            Swal.fire({
                icon: 'success',
                title: 'تم اختيار الفئة',
                text: `الفئة: ${selectedCategory}`
            });

            fetchFilteredProducts();
        }
//     function disable_cart_fragments() {
//     if (!is_admin()) {
//         wp_dequeue_script('wc-cart-fragments');
//     }
// }
//     add_action('wp_enqueue_scripts', 'disable_cart_fragments', 11);


    function fetchFilteredProducts() {
    if (selectedCarSize && selectedCategory) {
        console.log('جاري جلب المنتجات...');
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'get_filtered_products',
                car_size: selectedCarSize,
                category: selectedCategory
            },
            success: function(response) {
                console.log('تم جلب المنتجات بنجاح');
                document.getElementById('products-container').innerHTML = response;
                addProductCardClickEvents(); // إعادة تفعيل الأحداث للبطاقات الجديدة
                updateCheckoutVisibility(); // تحديث حالة Checkout بعد جلب المنتجات
            },
            error: function(xhr, status, error) {
                console.error('خطأ في جلب المنتجات:', error);
            }
        });
    } else {
        console.warn('يرجى اختيار حجم السيارة والفئة');
    }
}

    function refreshWooCommerceFragments() {
    jQuery.ajax({
        url: '/?wc-ajax=get_refreshed_fragments',
        method: 'POST',
        success: function (response) {
            if (response && response.fragments) {
                jQuery.each(response.fragments, function (key, value) {
                    jQuery(key).replaceWith(value);
                });

                console.log('تم تحديث WooCommerce Fragments.');
                
                // updateWooCommerceCheckout();
            }
        },
        error: function () {
            console.error('خطأ في تحديث WooCommerce Fragments.');
        }
    });
}

    function updateWooCommerceCheckout() {
    const checkoutWrapper = jQuery('.woocommerce-checkout');
    if (checkoutWrapper.length > 0) {
        checkoutWrapper.load(window.location.href + ' .woocommerce-checkout > *', function () {
            console.log('تم تحديث قسم WooCommerce Checkout.');
        });
    } else {
        // إذا لم يكن موجودًا، تأكد من إضافته
        const checkoutSection = document.getElementById('checkout-section');
        if (checkoutSection) {
            checkoutSection.style.display = 'block'; // عرض القسم
            jQuery('#checkout-section').load(window.location.href + ' #checkout-section > *');
        }
    }
}
    function addProductCardClickEvents() {
            document.querySelectorAll('.product-card').forEach(card => {
                card.addEventListener('click', function() {
                    toggleCart(card.getAttribute('data-product-id'), card);
                });
            });
        }


    </script>
    <?php
    return ob_get_clean();
}
 function disable_cart_fragments() {
    if (!is_admin()) {
        wp_dequeue_script('wc-cart-fragments');
    }
}
    add_action('wp_enqueue_scripts', 'disable_cart_fragments', 11);

// Ajax to fetch products based on car size and category
function get_filtered_products() {
    $car_size = isset($_POST['car_size']) ? sanitize_text_field($_POST['car_size']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    if (!$car_size || !$category) {
        echo '<p style="color: white; text-align: center;">يرجى اختيار الحجم والفئة.</p>';
        wp_die();
    }

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'product_cat',
                'field' => 'name',
                'terms' => $car_size,
            ),
            array(
                'taxonomy' => 'product_cat',
                'field' => 'name',
                'terms' => $category,
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            $warranty_text = '';

            // قائمة التصنيفات المرتبطة بالضمان
           $warranty_terms = array(
    'ضمان 10 سنوات' => 'ضمان 10 سنوات (أمريكي)',
    'ضمان 10 سنوات (بريطانى)' => 'ضمان 10 سنوات (بريطاني)',
    'ضمان 10 سنوات (بريطاني – امريكى)' => 'ضمان 10 سنوات (بريطاني – أمريكي)',
    'ضمان 6 سنوات' => 'ضمان 6 سنوات (بريطاني)',
    'ضمان 7 سنوات' => 'ضمان 7 سنوات (بريطاني – كوري)',
    'ضمان ثلاث سنوات' => 'ضمان ثلاث سنوات',
    'ضمان عام واحد' => 'ضمان عام واحد',
);

foreach ($warranty_terms as $term_slug => $term_display) {
    if (has_term($term_slug, 'product_cat', get_the_ID())) {
        $warranty_text = $term_display;
        break;
    }
}


            // عرض المنتج
            ?>
            <div class="product-card <?php echo WC()->cart->find_product_in_cart(WC()->cart->generate_cart_id(get_the_ID())) ? 'in-cart' : ''; ?>" data-product-id="<?php echo get_the_ID(); ?>">
                <!-- صورة المنتج -->
                <div class="product-image">
                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php echo get_the_title(); ?>">
                </div>
                <!-- تفاصيل المنتج -->
                <div class="product-details">
                    <h3 class="product-title"><?php echo get_the_title(); ?></h3>
                    <?php if (!empty($warranty_text)) : ?>
                        <div class="warranty">
                            <img src="https://dettaglioauto.sa/img/service_proxy_key.svg" alt="warranty">
                            <span><?php echo $warranty_text; ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="price">
                        <span class="value"><?php echo wc_price($product->get_price()); ?></span>
                        <span>ريال</span>
                    </div>
                    <div class="installment">
                        <span>قسمها على 4 دفعات</span>
                        <img src="https://dettaglioauto.sa/img/tabby-logo.png" alt="Tabby">
                        <img src="https://dettaglioauto.sa/img/tamara-logo.png" alt="Tamara">
                    </div>
                    <a href="#" class="more-details">المزيد من التفاصيل</a>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p style="color: white; text-align: center;">لا توجد منتجات مطابقة.</p>';
    }

    wp_die();
}

add_action('wp_ajax_get_filtered_products', 'get_filtered_products');
add_action('wp_ajax_nopriv_get_filtered_products', 'get_filtered_products');

// Ajax to add/remove product from cart
function toggle_cart_item() {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
    }

    $cart = WC()->cart;

    if ($cart->find_product_in_cart($cart->generate_cart_id($product_id))) {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                $cart->remove_cart_item($cart_item_key);
                wp_send_json_success(array('added' => false));
            }
        }
    } else {
        $cart->add_to_cart($product_id);
        wp_send_json_success(array('added' => true));
    }

    wp_die();
}
add_action('wp_ajax_toggle_cart_item', 'toggle_cart_item');
add_action('wp_ajax_nopriv_toggle_cart_item', 'toggle_cart_item');

/**
 * ftech functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ftech
 */

define('FTECH_THEME_DRI', get_template_directory());
define('FTECH_INC_DRI', get_template_directory() . '/inc/');
define('FTECH_THEME_URI', get_template_directory_uri());
define('FTECH_CSS_PATH', FTECH_THEME_URI . '/assets/css');
define('FTECH_JS_PATH', FTECH_THEME_URI . '/assets/js');
define('FTECH_IMG_PATH', FTECH_THEME_URI . '/assets/images');
define('Ftech_Admin_DRI', FTECH_THEME_DRI . '/admin');

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function ftech_setup(){
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on ftech, use a find and replace
	 * to change 'ftech' to the name of your theme in all the template files.
	 */
	load_theme_textdomain('ftech', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');
	add_image_size('ftech-img-size-1', 435, 323, true);
	add_image_size('ftech-img-size-2', 733, 465, true);
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');
	remove_theme_support('widgets-block-editor');
	add_filter( 'big_image_size_threshold', '__return_false' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support('post-thumbnails');

	//Woocommerc
	add_theme_support('woocommerce');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__('Primary', 'ftech'),
		)
	);

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'ftech_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height' => 250,
			'width' => 250,
			'flex-width' => true,
			'flex-height' => true,
		)
	);
}
add_action('after_setup_theme', 'ftech_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function ftech_content_width(){
	$GLOBALS['content_width'] = apply_filters('ftech_content_width', 640);
}
add_action('after_setup_theme', 'ftech_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function ftech_widgets_init(){
	register_sidebar(
		array(
			'name' => esc_html__('Sidebar', 'ftech'),
			'id' => 'sidebar-1',
			'description' => esc_html__('Add widgets here.', 'ftech'),
			'before_widget' => '<div id="%1$s" class="%2$s sidebar-box mb-30 wow fadeInUp">',
			'after_widget' => '</div>',
			'before_title' => '<h4 class="sidebar-box-title ftc-heading-1">',
			'after_title' => '</h4>',
		)
	);
	register_sidebar(
		array(
			'name' => esc_html__('Shop Siderbar', 'ftech'),
			'id' => 'shop-sidebar-1',
			'description' => esc_html__('Add widgets here.', 'ftech'),
			'before_widget' => '<div id="%1$s" class="widget mt-30 %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h2 class="widget__title">',
			'after_title' => '</h2>',
		)
	);
}
add_action('widgets_init', 'ftech_widgets_init');



/**
 *Google Font Load 
 */
if (!function_exists('ftech_fonts_url')):

	function ftech_fonts_url(){
		$fonts_url = '';
		$font_families = array();
		$subsets = 'latin';
		if ('off' !== _x('on', 'Unbounded: on or off', 'ftech')) {
			$font_families[] = 'Unbounded:200,300,400,500,600,700,800,900';
		}
		if ('off' !== _x('on', 'Ubuntu: on or off', 'ftech')) {
			$font_families[] = 'Ubuntu:300,300i,400,400i,500,500i,700,700i';
		}
		if ('off' !== _x('on', 'Saira: on or off', 'ftech')) {
			$font_families[] = 'Saira:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i';
		}
		if ('off' !== _x('on', 'Roboto: on or off', 'ftech')) {
			$font_families[] = 'Roboto:100,100i,200,200i,400,400i,500,500i,700,700i,900,900i';
		}
		if ('off' !== _x('on', 'Galada: on or off', 'ftech')) {
			$font_families[] = 'Galada:400';
		}
		
		if ($font_families) {
			$fonts_url = add_query_arg(
				array(
					'family' => urlencode(implode('|', $font_families)),
					'subset' => urlencode($subsets),
				),
				'https://fonts.googleapis.com/css'
			);
		}

		return esc_url_raw($fonts_url);
	}
endif;


/**
 * Enqueue scripts and styles.
 */
function ftech_scripts(){

	wp_enqueue_style('ftech-google-fonts', ftech_fonts_url(), array(), null);

	wp_enqueue_style('bootstrap', FTECH_CSS_PATH . '/bootstrap.min.css');
	wp_enqueue_style('all-min', FTECH_CSS_PATH . '/all.min.css');
	wp_enqueue_style('e-animations', FTECH_CSS_PATH . '/animate.css');
	wp_enqueue_style('flaticon-one', FTECH_CSS_PATH . '/flaticon_new_it_solution.css');
	wp_enqueue_style('image-reveal', FTECH_CSS_PATH . '/image-reveal.css');
	wp_enqueue_style('swiper-ftech', FTECH_CSS_PATH . '/swiper.min.css');
	wp_enqueue_style('magnific-popup', FTECH_CSS_PATH . '/magnific-popup.css');
	wp_enqueue_style('ftech-main', FTECH_CSS_PATH . '/main.css');

	if (class_exists('WooCommerce')) {
		wp_enqueue_style('woocommerce-style', get_template_directory_uri() . '/woocommerce/woocommerce.css');
	}

	$your_curnt_lang = apply_filters('wpml_current_language', NULL);
	if (is_rtl() && $your_curnt_lang != 'en') {
		wp_enqueue_style('binsro-rtl', FTECH_CSS_PATH . '/rtl.css');
	}

	wp_enqueue_style('ftech-style', get_stylesheet_uri(), array());

	wp_enqueue_script( 'imagesloaded', ['jquery'], false, true );
    wp_enqueue_script( 'jquery-ui-core', ['jquery'], false, true );
	wp_enqueue_script('bootstrap-bundle', FTECH_JS_PATH . '/bootstrap.bundle.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('swiper-bundle', FTECH_JS_PATH . '/swiper-bundle.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('wow', FTECH_JS_PATH . '/wow.js', array('jquery'), '1.0', true);
	wp_enqueue_script('magnific-popup', FTECH_JS_PATH . '/magnific-popup.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('SplitText', FTECH_JS_PATH . '/SplitText.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('reveal', FTECH_JS_PATH . '/reveal.js', array('jquery', 'imagesloaded'), '1.0', true);
	wp_enqueue_script('matter', FTECH_JS_PATH . '/matter.js', array('jquery', 'imagesloaded'), '1.0', true);
	wp_enqueue_script('throwable', FTECH_JS_PATH . '/throwable.js', array('jquery', 'imagesloaded'), '1.0', true);
	wp_enqueue_script('gsap', FTECH_JS_PATH . '/gsap.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('nice-select', FTECH_JS_PATH . '/nice-select.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('counterup', FTECH_JS_PATH . '/counterup.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('waypoints', FTECH_JS_PATH . '/waypoints.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('lenis', FTECH_JS_PATH . '/lenis.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('ScrollTrigger', FTECH_JS_PATH . '/ScrollTrigger.min.js', array('jquery'), '1.0', true);
	
	wp_enqueue_script('ftech-main', FTECH_JS_PATH . '/main.js', array('jquery'), '1.0', true);


	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'ftech_scripts');

/**
 * Implement the Custom Header feature.
 */
require FTECH_THEME_DRI . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require FTECH_THEME_DRI . '/inc/template-tags.php';

/**
 * Custom template tags for this theme.
 */
require FTECH_THEME_DRI . '/inc/class-wp-ftech-navwalker.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require FTECH_THEME_DRI . '/inc/template-functions.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require FTECH_THEME_DRI . '/inc/ftech-functions.php';

/**
 * Cs Fremwork Config
 */
require FTECH_THEME_DRI . '/inc/cs-framework-functions.php';

/**
 * Dynamic Style
 */
require FTECH_THEME_DRI . '/inc/dynamic-style.php';

/**
 * ftech Core Functions
 */
require FTECH_THEME_DRI . '/inc/ftech-helper-class.php';

/**
 * ftech Core Functions
 */
require FTECH_THEME_DRI . '/inc/admin/class-admin-dashboard.php';

/**
 * ftech Core Functions
 */
require FTECH_THEME_DRI . '/inc/admin/demo-import/functions.php';

/**
 * Customizer additions.
 */
require FTECH_THEME_DRI . '/inc/customizer.php';


/**
 * Initial Breadcrumb
 */
require FTECH_THEME_DRI . '/inc/breadcrumb-init.php';



/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require FTECH_THEME_DRI . '/inc/jetpack.php';
}
