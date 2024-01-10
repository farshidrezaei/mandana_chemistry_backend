<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'callback' => 'مقدار ورودی آدرس بازگشتی معتبر نمی‌باشد',
    'bank_card_number' => 'شماره کارت وارد شده معتبر نمی‌باشد',
    'accepted' => ':attribute باید پذیرفته شده باشد.',
    'active_url' => 'آدرس :attribute معتبر نیست',
    'after' => ':attribute باید تاریخی بعد از :date باشد.',
    'after_or_equal' => ':attribute باید تاریخی بعد از :date، یا مطابق با آن باشد.',
    'alpha' => ':attribute باید فقط حروف الفبا باشد.',
    'alpha_dash' => ':attribute باید فقط حروف الفبا، عدد و خط تیره(-) باشد.',
    'alpha_num' => ':attribute باید فقط شامل حروف الفبا و عدد باشد.',
    'array' => ':attribute باید آرایه باشد.',
    'before' => ':attribute باید تاریخی قبل از :date باشد.',
    'before_or_equal' => ':attribute باید تاریخی قبل از :date، یا مطابق با آن باشد.',
    'between' => [
        'numeric' => ':attribute باید بین :min و :max باشد.',
        'file' => ':attribute باید بین :min و :max کیلوبایت باشد.',
        'string' => ':attribute باید بین :min و :max کاراکتر باشد.',
        'array' => ':attribute باید بین :min و :max مورد باشد.',
    ],
    'boolean' => 'فیلد :attribute فقط می‌تواند صحیح و یا غلط باشد',
    'confirmed' => ':attribute با فیلد تکرار مطابقت ندارد.',
    'date' => ':attribute یک تاریخ معتبر نیست.',
    'date_format' => ':attribute با الگوی :format مطاقبت ندارد.',
    'different' => ':attribute و :other باید متفاوت باشند.',
    'digits' => ':attribute باید :digits رقم باشد.',
    'digits_between' => ':attribute باید بین :min و :max رقم باشد.',
    'dimensions' => 'ابعاد تصویر :attribute قابل قبول نیست.',
    'distinct' => 'فیلد :attribute تکراری است.',
    'email' => ':attribute باید یک ایمیل معتبر باشد',
    'exists' => ':attribute انتخاب شده، معتبر نیست.',
    'file' => ':attribute باید یک فایل باشد',
    'filled' => 'فیلد :attribute الزامی است',
    'image' => ':attribute باید تصویر باشد.',
    'in' => ':attribute انتخاب شده، معتبر نیست.',
    'in_array' => 'فیلد :attribute در :other وجود ندارد.',
    'integer' => ':attribute باید عدد صحیح باشد.',
    'ip' => ':attribute باید IP معتبر باشد.',
    'ipv4' => ':attribute باید یک آدرس معتبر از نوع IPv4 باشد.',
    'ipv6' => ':attribute باید یک آدرس معتبر از نوع IPv6 باشد.',
    'json' => 'فیلد :attribute باید یک رشته از نوع JSON باشد.',
    'max' => [
        'numeric' => ':attribute نباید بزرگتر از :max باشد.',
        'file' => ':attribute نباید بزرگتر از :max کیلوبایت باشد.',
        'string' => ':attribute نباید بیشتر از :max کاراکتر باشد.',
        'array' => ':attribute نباید بیشتر از :max مورد باشد.',
    ],
    'mimes' => ':attribute باید یکی از فرمت های :values باشد.',
    'mimetypes' => ':attribute باید یکی از فرمت های :values باشد.',
    'min' => [
        'numeric' => ':attribute نباید کوچکتر از :min باشد.',
        'file' => ':attribute نباید کوچکتر از :min کیلوبایت باشد.',
        'string' => ':attribute نباید کمتر از :min کاراکتر باشد.',
        'array' => ':attribute نباید کمتر از :min مورد باشد.',
    ],
    'gt' => [
        'numeric' => ':attribute باید بزرگتر از  :value باشد',
        'file' => 'سایز :attribute باید بزرگتر از :value کیلوبایت باشد.',
        'string' => 'تعداد کارکتر :attribute باید بیشتر از :value باشد.',
        'array' => ' تعداد موردهای :attribute باید بیشتر از :value باشد.',
    ],
    'gte' => [
        'numeric' => ':attribute باید بزرگتر/برابر از  :value باشد',
        'file' => 'سایز :attribute باید بزرگتر/برابر از :value کیلوبایت باشد.',
        'string' => 'تعداد کارکتر :attribute باید بیشتر/برابر از :value باشد.',
        'array' => ' تعداد موردهای :attribute باید بیشتر/برابر از :value باشد.',
    ],
    'not_in' => ':attribute انتخاب شده، معتبر نیست.',
    'numeric' => ':attribute باید عدد باشد.',
    'present' => 'فیلد :attribute باید در پارامترهای ارسالی وجود داشته باشد.',
    'regex' => 'فرمت :attribute معتبر نیست',
    'required' => 'وارد کردن :attribute الزامی است',
    'required_if' => 'هنگامی که :other برابر با :value است، فیلد :attribute الزامی است.',
    'required_unless' => 'فیلد :attribute ضروری است، مگر آنکه :other در :values موجود باشد.',
    'required_with' => 'در صورت وجود فیلد :values، فیلد :attribute الزامی است.',
    'required_with_all' => 'در صورت وجود فیلدهای :values، فیلد :attribute الزامی است.',
    'required_without' => 'در صورت عدم وجود فیلد :values، فیلد :attribute الزامی است.',
    'required_without_all' => 'در صورت عدم وجود هر یک از فیلدهای :values، فیلد :attribute الزامی است.',
    'same' => ':attribute و :other باید مانند هم باشند.',
    'size' => [
        'numeric' => ':attribute باید برابر با :size باشد.',
        'file' => ':attribute باید برابر با :size کیلوبایت باشد.',
        'string' => ':attribute باید برابر با :size کاراکتر باشد.',
        'array' => ':attribute باشد شامل :size مورد باشد.',
    ],
    'string' => 'فیلد :attribute باید متن باشد.',
    'timezone' => 'فیلد :attribute باید یک منطقه زمانی قابل قبول باشد.',
    'unique' => ':attribute قبلا انتخاب شده است.',
    'uploaded' => 'آپلود فایل :attribute موفقیت آمیز نبود.',
    'url' => 'فرمت آدرس :attribute اشتباه است.',
    'uuid' => 'مقدار :attribute باید با فرمت UUID باشد.',

    'license' => 'پلاک وارد شده صحیح نمی‌باشد.',
    'license_police' => 'پلاک وارد شده برای سرویس‌های پلیس صحیح نمی‌باشد.',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'license' => 'پلاک',
        'taxi_code' => 'شماره پروانه تاکسیرانی',
        'bank_card_number' => 'شماره کارت',
        'first_name' => 'نام',
        'last_name' => 'نام خانوادگی',
        'request_type' => 'نوع درخواست',
        'price' => 'مبلغ',
        'car_model' => 'مدل خودرو',
        'color_id' => 'رنگ',
        'car_ad_category_id' => 'دسته خودرو',
        'time' => 'زمان',
        'card_front_image' => 'تصویر جلو کارت خودرو',
        'card_back_image' => 'تصویر پشت کارت خودرو',
        'license_front_image' => 'تصویر جلو گواهی‌نامه',
        'license_back_image' => 'تصویر پشت گواهی‌نامه',
        'discount_transfer_supplement_image' => 'تصویر الحاقیه انتقال تخفیف',
        'national_code' => 'کد ملی',
        'owner_mobile' => 'موبایل مالک',
        'bank_code' => 'بانک',
        'count' => 'تعداد',
        'amount' => 'مبلغ',
        'wallet_charge_amount' => 'مبلغ شارژ کیف پول',
        'wallet_min_threshold' => 'حداقل مقدار اعتبار',
        'expiration_months' => 'پایان قرارداد',
        'type' => 'نوع',
        'description' => 'توضیحات',
        'bill_id' => 'شناسه قبض',
        'payment_id' => 'شناسه پرداخت',
        'real_price' => 'قیمت واقعی',
        'file' => 'پرونده بارگذاری شده',
        'file.0' => 'پرونده بارگذاری شده اول',
        'file.1' => 'پرونده بارگذاری شده دوم',
        'file.2' => 'پرونده بارگذاری شده سوم',
        'file.3' => 'پرونده بارگذاری شده چهارم',
        'file.4' => 'پرونده بارگذاری شده پنجم',
        'benefits' => 'مزایا',
        'benefits.*.title' => 'عنوان مزیت',
        'required_docs' => 'مدارک مورد نیاز',
        'required_docs.*.title' => 'عنوان مدرک مورد نیاز',
        'branches' => 'شعب',
        'branches.*.name' => 'نام شعبه',
        'branches.*.address' => 'آدرس شعبه',
        'branches.*.phone' => 'تلفن شعبه',
        'phone' => 'تلفن',
        'address' => 'آدرس',
        'name' => 'نام',
        'title' => 'عنوان',
        'image' => 'تصویر',
        'item' => 'گزینه',
        'is_active' => 'فعال بودن',
        'start_at' => 'زمان شروع سرویس',
        'finish_at' => 'زمان پایان سرویس',
        'max_allowed_requests' => 'حداکثر تعداد درخواست',
        'reference_type' => 'نوع سرویس',
        'starting_hour' => 'ساعت شروع رزرو',
        'closing_hour' => 'ساعت پایان رزرو',
        'reserve_per_slot' => 'بازه زمانی رزرو (دقیقه)',
        'slot_interval_minutes' => 'تعداد رزرو به ازای بازه زمانی',
        'reference_letter_body' => 'متن نامه ارجاع',
        'sms_body' => 'متن پیامک',
        'has_reference_letter' => 'وضعیت وجود نامه ارجاع',
        'has_sms' => 'وضعیت ارسال پیامک',
    ],

];
