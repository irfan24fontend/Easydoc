অফলাইন প্রেসক্রিপশন ফিচার — ইনস্টল নির্দেশনা
====================================================

এই zip-টা তোমার Laravel প্রজেক্টের রুটে extract করলে নিচের ফাইলগুলো
সঠিক জায়গায় বসে যাবে (আগেরগুলো replace হবে):

  resources/views/doctor/prescriptions/create.blade.php
  public/js/rx-offline.js                      (নতুন)
  public/sw.js                                  (নতুন)
  database/migrations/2026_10_28_000000_add_offline_ref_to_prescriptions_table.php  (নতুন)
  app/Models/Prescription.php
  app/Services/PrescriptionService.php
  app/Http/Requests/StorePrescriptionRequest.php
  app/Http/Controllers/Doctor/PrescriptionController.php

যা এই zip extract করলে *হবে না*, নিজে হাতে করতে হবে
----------------------------------------------------
১) routes/web.php — "prescriptions.store" রুটের ঠিক নিচে এই লাইনটা যোগ করো
   (পুরো routes/web.php ফাইল zip-এ দেওয়া হয়নি, কারণ ওখানে আরও অনেক
   অসম্পর্কিত রুট আছে যেগুলো আমি দেখিনি — পুরোটা বদলে দিলে সেগুলো হারানোর
   ঝুঁকি থাকত):

   Route::post('prescriptions/sync-offline', [PrescriptionController::class, 'syncOffline'])
       ->name('prescriptions.sync-offline');

   এটা বসবে এর ঠিক নিচে:
   Route::post('prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');

২) মাইগ্রেশন চালাও:
   php artisan migrate

৩) extract করার পর ব্রাউজারে ক্যাশ পরিষ্কার করে (hard refresh / Ctrl+Shift+R)
   একবার অনলাইনে থাকতে প্রেসক্রিপশন create পেজটা খুলো — যাতে নতুন
   service worker আর ব্যালেন্স ক্যাশ হয়ে যায়।

৪) টেস্ট করবে কীভাবে
   - পেজ খোলা অবস্থায় Chrome DevTools > Network > "Offline" টিক দাও
   - একটা প্রেসক্রিপশন বানিয়ে Save করো — লাল ব্যানার, অফলাইন প্রিন্ট আসার কথা
   - "Offline" টিক তুলে দাও — কয়েক সেকেন্ডে নিজে থেকে sync হয়ে সবুজ ব্যানার/toast আসার কথা
   - outbox বাটনে চেপে sync-এর status দেখা যাবে

এই ফিচারের পুরো বিস্তারিত ব্যাখ্যা (কীভাবে কাজ করে, সীমাবদ্ধতা) আগের চ্যাট
মেসেজে লেখা আছে।
