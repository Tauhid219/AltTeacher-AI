# প্রজেক্ট এনালাইসিস রিপোর্ট (AltTeacher-AI)

এই ডকুমেন্টটিতে **AltTeacher-AI** প্রজেক্টের বর্তমান অবস্থা, ব্যবহৃত টেকনোলজি স্ট্যাক, ফাইল কাঠামো এবং পরবর্তী উন্নয়নমূলক কাজের জন্য প্রয়োজনীয় দিকনির্দেশনা বিশ্লেষণ করা হয়েছে।

---

## ১. প্রযুক্তিগত কাঠামো (Technology Stack)

প্রজেক্টের মূল ফাইলসমূহ (যেমন: [composer.json](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/composer.json) এবং [package.json](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/package.json)) এনালাইসিস করে নিম্নোক্ত টেকনোলজি স্ট্যাক পাওয়া গিয়েছে:

| উপাদান | প্রযুক্তি/সংস্করণ | বিবরণ |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel v12.0 | লারাভেলের সর্বশেষ সংস্করণ |
| **PHP Version** | ^8.2 | আধুনিক PHP সিনট্যাক্স সমর্থিত |
| **Frontend Styling** | Tailwind CSS v4.0.0 | Tailwind-এর লেটেস্ট v4 ইঞ্জিনের সাথে `@tailwindcss/vite` ইন্টিগ্রেশন |
| **Asset Bundler** | Vite v7.0.7 | দ্রুত ফ্রন্টএন্ড বিল্ড এবং হট রিলোডিং-এর জন্য |
| **HTTP Client** | Axios v1.11.0 | ফ্রন্টএন্ড থেকে ব্যাকএন্ডে API রিকোয়েস্ট পাঠানোর জন্য |
| **Testing Framework** | PHPUnit v11.5.50 | ইউনিট এবং ফিচার টেস্ট সম্পন্ন করার জন্য |
| **Helper / Dev Tools** | `laravel/boost` (v2.2), `laravel/pail` | কাজের গতি বাড়াতে ও সহজে ডিবাগ করতে ব্যবহৃত |

---

## ২. ডাটাবেজ কনফিগারেশন (Database Configuration)

প্রজেক্টের [.env](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/.env) ফাইল অনুযায়ী ডাটাবেজ কনফিগারেশন নিম্নরূপ:

*   **Connection Driver:** MySQL (`mysql`)
*   **Host IP / Port:** `127.0.0.1` : `3306`
*   **Database Name:** `altteacher_ai`
*   **Database Username:** `root`
*   **Database Password:** *খালি (Empty)*

> [!NOTE]
> প্রজেক্টের বর্তমান পাথ `c:\xampp\htdocs\...` ইঙ্গিত করে এটি লোকাল XAMPP সার্ভারে রান করা হচ্ছে। তাই XAMPP-এর MySQL সচল রেখে `altteacher_ai` নামে একটি ডাটাবেজ তৈরি করে নেওয়া জরুরি।

---

## ৩. গুরুত্বপূর্ণ ডিরেক্টরি এবং ফাইলের তালিকা (Key Files Directory)

প্রজেক্টের বর্তমান ফাইলসমূহ নিচে লিঙ্ক সহ উল্লেখ করা হলো:

*   **রাউটিং:** [routes/web.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/routes/web.php) — বর্তমানে শুধুমাত্র লারাভেলের ডিফল্ট হোমপেজ রাউটটি ডিফাইন করা আছে।
*   **হোমপেজ ভিউ:** [welcome.blade.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/views/welcome.blade.php) — লারাভেল ১২-এর ডিফল্ট ইন্টারঅ্যাক্টিভ ফ্রন্টএন্ড ভিউ।
*   **ইউজার মডেল:** [app/Models/User.php](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/app/Models/User.php) — ডিফল্ট অথেনটিকেশন এবং ইউজার মডেল।
*   **কনফিগারেশন ফাইল:** [config/](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/config/) ডিরেক্টরি — যেখানে সেশন, ডাটাবেজ ও মেইল কনফিগারেশন ফাইলগুলো রয়েছে।
*   **CSS স্টাইলশিট:** [resources/css/app.css](file:///c:/xampp/htdocs/My%20Works/Infinity%20AI%20Buildfest%202026/AltTeacher-AI/resources/css/app.css) — যেখানে Tailwind CSS v4 ডিরেক্টিভ ইনপুট করা হয়েছে।

---

## ৪. বর্তমান প্রজেক্টের অবস্থা বিশ্লেষণ (Current State Analysis)

এটি বর্তমানে একটি **সম্পূর্ণ ফ্রেশ এবং ক্লিন লারাভেল ১২ স্কেলিটন (Laravel Skeleton)** প্রজেক্ট। এখনও পর্যন্ত কোনো কাস্টম ফিচার, এপিআই, ব্যাকএন্ড কন্ট্রোলার বা নতুন ভিউ তৈরি করা হয়নি। 

ডাটাবেজে বর্তমানে ডিফল্ট ৩টি মাইগ্রেশন ফাইল রয়েছে:
1.  `0001_01_01_000000_create_users_table.php` (ইউজার টেবিল)
2.  `0001_01_01_000001_create_cache_table.php` (ক্যাশ ম্যানেজমেন্ট টেবিল)
3.  `0001_01_01_000002_create_jobs_table.php` (কিউ/জব টেবিল)

---

## ৫. পরবর্তী প্রস্তাবিত পদক্ষেপ (Recommended Next Steps)

প্রজেক্টটি শুরু করার জন্য নিচের ধাপগুলো অনুসরণ করার পরামর্শ দেওয়া হচ্ছে:

1.  **ডাটাবেজ মাইগ্রেশন সম্পন্ন করা:** 
    লোকাল MySQL সার্ভারে `altteacher_ai` ডাটাবেজ তৈরি করে টার্মিনালে `php artisan migrate` রান করতে হবে।
2.  **অথেনটিকেশন সিস্টেম ইন্সটল করা (ঐচ্ছিক কিন্তু সাজেস্টেড):** 
    লারাভেলের ডিফল্ট অথেনটিকেশনের জন্য **Laravel Breeze** ব্যবহার করা যেতে পারে, যা Tailwind CSS v4-এর সাথে দারুণভাবে মানিয়ে যাবে।
3.  **প্রজেক্ট স্কোপিং বা ফিচারের সংজ্ঞা নির্ধারণ:**
    **AltTeacher-AI** মূলত কী ধরণের কাজ করবে (যেমন: AI-চালিত কুইজ জেনারেটর, অ্যাসাইনমেন্ট ফিডব্যাক, লার্নিং রিকমেন্ডেশন সিস্টেম) তার একটি সুনির্দিষ্ট রূপরেখা বা রিকোয়ারমেন্ট তৈরি করা প্রয়োজন।

---
*রিপোর্ট জেনারেট করার তারিখ: ০২ জুন, ২০২৬*
