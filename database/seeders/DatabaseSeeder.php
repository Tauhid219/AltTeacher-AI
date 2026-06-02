<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\District;
use App\Models\SchoolProfile;
use App\Models\TeacherProfile;
use App\Models\Credential;
use App\Models\SubstituteJob;
use App\Models\Booking;
use App\Models\Timesheet;
use App\Models\LessonPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed Roles
        $districtAdminRole = Role::create(['name' => 'district_admin']);
        $schoolAdminRole = Role::create(['name' => 'school_admin']);
        $teacherRole = Role::create(['name' => 'teacher']);

        // 1. Seed Districts
        $springfieldDistrict = District::create([
            'name' => 'Springfield School District 401',
            'state' => 'IL',
            'compliance_rules' => [
                'max_weekly_hours' => 30,
                'requires_background_check' => true,
                'required_credentials' => ['state_teaching_license', 'background_check'],
            ],
        ]);

        $shelbyvilleDistrict = District::create([
            'name' => 'Shelbyville Unified School District 502',
            'state' => 'IL',
            'compliance_rules' => [
                'max_weekly_hours' => 25,
                'requires_background_check' => true,
                'required_credentials' => ['state_teaching_license', 'background_check'],
            ],
        ]);

        // 2. Seed Users & Profiles

        // District Admins
        $districtAdminUser = User::create([
            'name' => 'Superintendent Gary Chalmers',
            'email' => 'chalmers@springfield.edu',
            'password' => Hash::make('password'),
        ]);
        $districtAdminUser->assignRole('district_admin');

        // School Admin 1 & Springfield Elementary School
        $springfieldAdminUser = User::create([
            'name' => 'Principal W. Seymour Skinner',
            'email' => 'skinner@springfield.edu',
            'password' => Hash::make('password'),
        ]);
        $springfieldAdminUser->assignRole('school_admin');
        $springfieldSchool = SchoolProfile::create([
            'user_id' => $springfieldAdminUser->id,
            'district_id' => $springfieldDistrict->id,
            'school_name' => 'Springfield Elementary School',
            'address' => '19 TAC Avenue, Springfield, IL 62704',
        ]);

        // School Admin 2 & Shelbyville Elementary School
        $shelbyvilleAdminUser = User::create([
            'name' => 'Principal Shelby Miller',
            'email' => 'miller@shelbyville.edu',
            'password' => Hash::make('password'),
        ]);
        $shelbyvilleAdminUser->assignRole('school_admin');
        $shelbyvilleSchool = SchoolProfile::create([
            'user_id' => $shelbyvilleAdminUser->id,
            'district_id' => $shelbyvilleDistrict->id,
            'school_name' => 'Shelbyville Elementary School',
            'address' => '404 Shelby Boulevard, Shelbyville, IL 62565',
        ]);

        // Teacher 1: Bob Terwilliger (Fully Approved)
        $teacherBobUser = User::create([
            'name' => 'Robert Underdunk Terwilliger',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
        ]);
        $teacherBobUser->assignRole('teacher');
        $teacherBobProfile = TeacherProfile::create([
            'user_id' => $teacherBobUser->id,
            'district_id' => $springfieldDistrict->id,
            'classroom_preferences' => [
                'grades' => ['Grade 6', 'Grade 7', 'Grade 8'],
                'subjects' => ['Mathematics', 'Science', 'Chemistry'],
                'preferred_schools' => [$springfieldSchool->id],
            ],
            'hourly_rate' => 35.00,
            'availability' => [
                'monday' => true,
                'tuesday' => true,
                'wednesday' => true,
                'thursday' => true,
                'friday' => true,
            ],
            'onboarding_status' => 'approved',
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherBobProfile->id,
            'document_type' => 'state_teaching_license',
            'document_path' => 'credentials/bob_license.pdf',
            'extracted_info' => [
                'license_number' => 'STL-992384',
                'name' => 'Robert Underdunk Terwilliger',
                'issue_date' => '2024-01-15',
                'state' => 'IL',
            ],
            'verification_status' => 'verified',
            'expiry_date' => Carbon::parse('2028-01-15'),
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherBobProfile->id,
            'document_type' => 'background_check',
            'document_path' => 'credentials/bob_background.pdf',
            'extracted_info' => [
                'case_number' => 'BC-883921',
                'result' => 'CLEARED',
                'completed_date' => '2025-05-10',
            ],
            'verification_status' => 'verified',
            'expiry_date' => Carbon::parse('2027-05-10'),
        ]);

        // Teacher 2: Herschel Krustofsky (Pending Onboarding)
        $teacherKrustyUser = User::create([
            'name' => 'Herschel Pinkus Krustofsky',
            'email' => 'krusty@example.com',
            'password' => Hash::make('password'),
        ]);
        $teacherKrustyUser->assignRole('teacher');
        $teacherKrustyProfile = TeacherProfile::create([
            'user_id' => $teacherKrustyUser->id,
            'district_id' => $springfieldDistrict->id,
            'classroom_preferences' => [
                'grades' => ['Grade 1', 'Grade 2', 'Grade 3'],
                'subjects' => ['Drama', 'Art', 'Music'],
                'preferred_schools' => [$springfieldSchool->id],
            ],
            'hourly_rate' => 25.00,
            'availability' => [
                'monday' => true,
                'wednesday' => true,
                'friday' => true,
            ],
            'onboarding_status' => 'pending',
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherKrustyProfile->id,
            'document_type' => 'state_teaching_license',
            'document_path' => 'credentials/krusty_license.pdf',
            'extracted_info' => [
                'license_number' => 'STL-773948',
                'name' => 'Herschel Pinkus Krustofsky',
                'issue_date' => '2025-02-20',
                'state' => 'IL',
            ],
            'verification_status' => 'verified',
            'expiry_date' => Carbon::parse('2029-02-20'),
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherKrustyProfile->id,
            'document_type' => 'background_check',
            'document_path' => 'credentials/krusty_background.pdf',
            'extracted_info' => null,
            'verification_status' => 'pending',
            'expiry_date' => null,
        ]);

        // Teacher 3: Lisa Simpson (Fully Approved - High Rate)
        $teacherLisaUser = User::create([
            'name' => 'Lisa Marie Simpson',
            'email' => 'lisa@example.com',
            'password' => Hash::make('password'),
        ]);
        $teacherLisaUser->assignRole('teacher');
        $teacherLisaProfile = TeacherProfile::create([
            'user_id' => $teacherLisaUser->id,
            'district_id' => $springfieldDistrict->id,
            'classroom_preferences' => [
                'grades' => ['Grade 1', 'Grade 5', 'Grade 8', 'Grade 12'],
                'subjects' => ['Mathematics', 'STEM', 'Music', 'History'],
                'preferred_schools' => [$springfieldSchool->id, $shelbyvilleSchool->id],
            ],
            'hourly_rate' => 40.00,
            'availability' => [
                'monday' => true,
                'tuesday' => true,
                'wednesday' => true,
                'thursday' => true,
                'friday' => true,
            ],
            'onboarding_status' => 'approved',
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherLisaProfile->id,
            'document_type' => 'state_teaching_license',
            'document_path' => 'credentials/lisa_license.pdf',
            'extracted_info' => [
                'license_number' => 'STL-448293',
                'name' => 'Lisa Marie Simpson',
                'issue_date' => '2026-03-01',
                'state' => 'IL',
            ],
            'verification_status' => 'verified',
            'expiry_date' => Carbon::parse('2031-03-01'),
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherLisaProfile->id,
            'document_type' => 'background_check',
            'document_path' => 'credentials/lisa_background.pdf',
            'extracted_info' => [
                'case_number' => 'BC-394829',
                'result' => 'CLEARED',
                'completed_date' => '2026-03-01',
            ],
            'verification_status' => 'verified',
            'expiry_date' => Carbon::parse('2028-03-01'),
        ]);

        // Teacher 4: Ned Flanders (Fully Approved - Shelbyville)
        $teacherNedUser = User::create([
            'name' => 'Nedward Flanders',
            'email' => 'ned@example.com',
            'password' => Hash::make('password'),
        ]);
        $teacherNedUser->assignRole('teacher');
        $teacherNedProfile = TeacherProfile::create([
            'user_id' => $teacherNedUser->id,
            'district_id' => $shelbyvilleDistrict->id,
            'classroom_preferences' => [
                'grades' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5'],
                'subjects' => ['History', 'English', 'Religious Studies'],
                'preferred_schools' => [$shelbyvilleSchool->id],
            ],
            'hourly_rate' => 30.00,
            'availability' => [
                'tuesday' => true,
                'wednesday' => true,
                'thursday' => true,
            ],
            'onboarding_status' => 'approved',
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherNedProfile->id,
            'document_type' => 'state_teaching_license',
            'document_path' => 'credentials/ned_license.pdf',
            'extracted_info' => [
                'license_number' => 'STL-552938',
                'name' => 'Nedward Flanders',
                'issue_date' => '2023-11-10',
                'state' => 'IL',
            ],
            'verification_status' => 'verified',
            'expiry_date' => Carbon::parse('2027-11-10'),
        ]);
        Credential::create([
            'teacher_profile_id' => $teacherNedProfile->id,
            'document_type' => 'background_check',
            'document_path' => 'credentials/ned_background.pdf',
            'extracted_info' => [
                'case_number' => 'BC-552930',
                'result' => 'CLEARED',
                'completed_date' => '2024-11-10',
            ],
            'verification_status' => 'verified',
            'expiry_date' => Carbon::parse('2026-11-10'),
        ]);

        // 3. Seed Substitute Jobs & Bookings & Timesheets

        // Upcoming/Open Job 1
        SubstituteJob::create([
            'school_profile_id' => $springfieldSchool->id,
            'subject' => 'Science',
            'grade_level' => 'Grade 5',
            'start_time' => '08:30:00',
            'end_time' => '15:30:00',
            'date' => Carbon::now()->addDays(2),
            'status' => 'open',
            'description' => 'Grade 5 Science replacement. Topics: Ecosystems and Photosynthesis.',
        ]);

        // Upcoming/Open Job 2
        SubstituteJob::create([
            'school_profile_id' => $springfieldSchool->id,
            'subject' => 'Music',
            'grade_level' => 'Grade 4',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'date' => Carbon::now()->addDays(3),
            'status' => 'open',
            'description' => 'Half-day replacement for Elementary School Band instructor.',
        ]);

        // Completed Job 3: Algebra Intro - Bob Terwilliger (Timesheet Approved)
        $completedJob1 = SubstituteJob::create([
            'school_profile_id' => $springfieldSchool->id,
            'subject' => 'Mathematics',
            'grade_level' => 'Grade 7',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'date' => Carbon::now()->subDays(3),
            'status' => 'completed',
            'description' => 'Sub needed for Grade 7 Math. Topic: Introduction to algebraic expressions.',
        ]);
        $booking1 = Booking::create([
            'substitute_job_id' => $completedJob1->id,
            'teacher_profile_id' => $teacherBobProfile->id,
            'status' => 'completed',
        ]);
        Timesheet::create([
            'booking_id' => $booking1->id,
            'check_in_time' => Carbon::now()->subDays(3)->setTime(7, 55, 0),
            'check_out_time' => Carbon::now()->subDays(3)->setTime(16, 5, 0),
            'calculated_hours' => 8.16, // roughly 8 hours 10 mins
            'calculated_pay' => 285.60, // 8.16 hours * $35.00
            'status' => 'approved',
        ]);
        LessonPlan::create([
            'booking_id' => $booking1->id,
            'original_plan_text' => "Original Teacher Notes:\n- Warm-up: 5 minutes review of fractions.\n- Lecture: Explain variable 'x' and coefficient.\n- Practice: Group exercises on page 42 (1 to 15).\n- Homework: Worksheet 7A.",
            'ai_summary' => "The substitute teacher will introduce variables and coefficients in Algebra for Grade 7. Students should complete practice questions on page 42 after a brief warm-up review.",
            'ai_generated_activities' => [
                'quizzes' => [
                    ['question' => 'What is the coefficient in the term 5x?', 'options' => ['x', '5', '5x', 'none'], 'answer' => '5'],
                    ['question' => 'If x = 3, what is the value of 2x + 4?', 'options' => ['6', '8', '10', '12'], 'answer' => '10'],
                ],
                'icebreakers' => [
                    'Math Bingo: Call out equations, students mark the values of x on their bingo cards.',
                    'Number Swap: Stand in a circle, count up, and replace multiples of 3 with "X".',
                ],
            ],
        ]);

        // Completed Job 4: Acting Exercises - Krusty (Timesheet Pending)
        $completedJob2 = SubstituteJob::create([
            'school_profile_id' => $springfieldSchool->id,
            'subject' => 'Drama',
            'grade_level' => 'Grade 3',
            'start_time' => '13:00:00',
            'end_time' => '16:00:00',
            'date' => Carbon::now()->subDays(5),
            'status' => 'completed',
            'description' => 'Afternoon Drama replacement. Exercises in physical theater and mime.',
        ]);
        $booking2 = Booking::create([
            'substitute_job_id' => $completedJob2->id,
            'teacher_profile_id' => $teacherKrustyProfile->id,
            'status' => 'completed',
        ]);
        Timesheet::create([
            'booking_id' => $booking2->id,
            'check_in_time' => Carbon::now()->subDays(5)->setTime(13, 2, 0),
            'check_out_time' => Carbon::now()->subDays(5)->setTime(16, 0, 0),
            'calculated_hours' => 2.97, // roughly 2 hours 58 mins
            'calculated_pay' => 74.25, // 2.97 hours * $25.00
            'status' => 'pending',
        ]);
        LessonPlan::create([
            'booking_id' => $booking2->id,
            'original_plan_text' => "Grade 3 physical theater exercises. Do mirror warmups, and simple roleplay stories.",
            'ai_summary' => "Substitute teacher will lead physical theater and roleplay storytelling exercises for Grade 3.",
            'ai_generated_activities' => [
                'quizzes' => [
                    ['question' => 'What is mime?', 'options' => ['Singing', 'Acting without words', 'Dancing', 'Drawing'], 'answer' => 'Acting without words'],
                ],
                'icebreakers' => [
                    'Mirror Game: Students pair up, one moves slowly and the other mirrors.',
                    'Emotional Symphony: One conductor points at students to act out different emotions.',
                ],
            ],
        ]);
    }
}
