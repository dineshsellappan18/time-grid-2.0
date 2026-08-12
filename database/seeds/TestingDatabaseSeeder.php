<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Humanresource;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class TestingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $spaOwner = $this->createUser('priya', 'Priya Sharma', 'priya@serenitysp.com', 'password');
        $spa = $this->createBusiness($spaOwner, [
            'name'           => 'Serenity Day Spa',
            'slug'           => 'serenity-day-spa',
            'description'    => 'A luxury day spa offering therapeutic massages, rejuvenating facials, and holistic body treatments. Escape the everyday and restore your inner balance.',
            'postal_address' => '42 Lavender Lane, Koramangala, Bangalore 560034',
            'phone'          => '+91 80 4567 8901',
            'timezone'       => 'Asia/Kolkata',
            'strategy'       => 'timeslot',
            'category_slug'  => 'spa',
        ]);

        $this->seedSpaServices($spa);
        $this->seedSpaStaff($spa);

        $restaurantOwner = $this->createUser('marco', 'Marco Rossi', 'marco@lapiazza.com', 'password');
        $restaurant = $this->createBusiness($restaurantOwner, [
            'name'           => 'La Piazza Ristorante',
            'slug'           => 'la-piazza',
            'description'    => 'Authentic Italian dining with handmade pasta, wood-fired pizzas, and a curated wine list. Perfect for date nights, family celebrations, and business dinners.',
            'postal_address' => '15 MG Road, Indiranagar, Bangalore 560038',
            'phone'          => '+91 80 2345 6789',
            'timezone'       => 'Asia/Kolkata',
            'strategy'       => 'timeslot',
            'category_slug'  => 'restaurant',
        ]);

        $this->seedRestaurantServices($restaurant);
        $this->seedRestaurantStaff($restaurant);

        $customers = $this->seedCustomers();

        $this->subscribeCustomersToBusiness($customers, $spa);
        $this->subscribeCustomersToBusiness(array_slice($customers, 0, 8), $restaurant);

        $this->seedVacancies($spa);
        $this->seedVacancies($restaurant);

        $this->seedAppointments($spa, $spaOwner, $customers);
        $this->seedAppointments($restaurant, $restaurantOwner, array_slice($customers, 0, 8));
    }

    private function createUser(string $username, string $name, string $email, string $password): User
    {
        return User::create([
            'username' => $username,
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt($password),
        ]);
    }

    private function createBusiness(User $owner, array $attrs): Business
    {
        $categorySlug = $attrs['category_slug'] ?? 'spa';
        unset($attrs['category_slug']);

        $category = Category::where('slug', $categorySlug)->first();
        if (!$category) {
            $category = Category::first();
        }

        $business = Business::create(array_merge([
            'category_id' => $category->id,
            'plan'        => 'free',
            'listed'      => true,
        ], $attrs));

        $business->owners()->save($owner);

        return $business;
    }

    private function seedSpaServices(Business $spa): void
    {
        $services = [
            ['name' => 'Swedish Massage',         'duration' => 60,  'description' => 'A classic full-body massage using long, flowing strokes to ease tension and promote relaxation.',        'color' => '#80BCA3'],
            ['name' => 'Deep Tissue Massage',      'duration' => 60,  'description' => 'Targeted pressure on deeper muscle layers to relieve chronic pain and tightness.',                      'color' => '#655643'],
            ['name' => 'Hot Stone Therapy',         'duration' => 90,  'description' => 'Heated basalt stones placed on key points to melt away stress and improve circulation.',               'color' => '#BF4D28'],
            ['name' => 'Hydrating Facial',          'duration' => 45,  'description' => 'A nourishing facial that deeply hydrates and restores your skin\'s natural glow.',                     'color' => '#E6AC27'],
            ['name' => 'Anti-Aging Facial',         'duration' => 60,  'description' => 'Advanced treatment with retinol and peptides to reduce fine lines and firm the skin.',                 'color' => '#6C2D58'],
            ['name' => 'Aromatherapy Session',      'duration' => 45,  'description' => 'Essential oil blends combined with gentle massage techniques for total mind-body harmony.',            'color' => '#B2577A'],
            ['name' => 'Couples Massage',           'duration' => 90,  'description' => 'Side-by-side relaxation massage for two in our private couples suite.',                                'color' => '#F6B17F'],
            ['name' => 'Express Mani-Pedi',         'duration' => 30,  'description' => 'Quick nail shaping, cuticle care, and polish for hands and feet.',                                     'color' => '#F6F7BD'],
        ];

        foreach ($services as $s) {
            $service = new Service($s);
            $service->business()->associate($spa);
            $service->save();
        }
    }

    private function seedRestaurantServices(Business $restaurant): void
    {
        $services = [
            ['name' => 'Dinner Reservation (2 guests)',   'duration' => 120, 'description' => 'Table for two with complimentary bread basket and house water.',                     'color' => '#BF4D28'],
            ['name' => 'Dinner Reservation (4 guests)',   'duration' => 120, 'description' => 'Table for four, ideal for double dates or small family gatherings.',                 'color' => '#E6AC27'],
            ['name' => 'Private Dining Room',             'duration' => 180, 'description' => 'Exclusive private room for up to 12 guests with a dedicated server and custom menu.', 'color' => '#6C2D58'],
            ['name' => 'Sunday Brunch',                   'duration' => 90,  'description' => 'All-you-can-eat brunch buffet with live pasta station and bottomless mimosas.',       'color' => '#F6B17F'],
            ['name' => 'Wine Tasting Experience',         'duration' => 60,  'description' => 'Guided tasting of 6 Italian wines paired with artisan cheese and charcuterie.',       'color' => '#655643'],
            ['name' => 'Chef\'s Table Experience',        'duration' => 150, 'description' => 'Sit at the chef\'s counter for a 7-course tasting menu with wine pairing.',           'color' => '#80BCA3'],
        ];

        foreach ($services as $s) {
            $service = new Service($s);
            $service->business()->associate($restaurant);
            $service->save();
        }
    }

    private function seedSpaStaff(Business $spa): void
    {
        $staff = [
            ['name' => 'Ananya Reddy',   'capacity' => 4],
            ['name' => 'Deepika Nair',   'capacity' => 4],
            ['name' => 'Kavitha Menon',  'capacity' => 3],
            ['name' => 'Ravi Kumar',     'capacity' => 3],
        ];

        foreach ($staff as $s) {
            $hr = new Humanresource($s);
            $hr->business()->associate($spa);
            $hr->save();
        }
    }

    private function seedRestaurantStaff(Business $restaurant): void
    {
        $staff = [
            ['name' => 'Chef Alessandro Bianchi', 'capacity' => 1],
            ['name' => 'Sommelier Lucia Conti',   'capacity' => 2],
            ['name' => 'Maître d\' Rajesh Pillai', 'capacity' => 6],
        ];

        foreach ($staff as $s) {
            $hr = new Humanresource($s);
            $hr->business()->associate($restaurant);
            $hr->save();
        }
    }

    /**
     * @return Contact[]
     */
    private function seedCustomers(): array
    {
        $customers = [
            ['firstname' => 'Aisha',    'lastname' => 'Patel',       'email' => 'aisha.patel@gmail.com',       'gender' => 'F', 'mobile' => '+91 98765 43210', 'birthdate' => '1990-03-14', 'postal_address' => '12A, Whitefield Main Road, Bangalore 560066'],
            ['firstname' => 'Rahul',    'lastname' => 'Mehta',       'email' => 'rahul.mehta@outlook.com',      'gender' => 'M', 'mobile' => '+91 98765 43211', 'birthdate' => '1985-07-22', 'postal_address' => '34, HSR Layout Sector 2, Bangalore 560102'],
            ['firstname' => 'Sneha',    'lastname' => 'Krishnan',    'email' => 'sneha.k@yahoo.com',            'gender' => 'F', 'mobile' => '+91 98765 43212', 'birthdate' => '1992-11-05', 'postal_address' => '78, Jayanagar 4th Block, Bangalore 560041'],
            ['firstname' => 'Vikram',   'lastname' => 'Singh',       'email' => 'vikram.singh@proton.me',       'gender' => 'M', 'mobile' => '+91 98765 43213', 'birthdate' => '1988-01-30', 'postal_address' => '56, Koramangala 5th Block, Bangalore 560095'],
            ['firstname' => 'Nandini',  'lastname' => 'Rao',         'email' => 'nandini.rao@gmail.com',        'gender' => 'F', 'mobile' => '+91 98765 43214', 'birthdate' => '1995-06-18', 'postal_address' => '22, Marathahalli Bridge Road, Bangalore 560037'],
            ['firstname' => 'Arjun',    'lastname' => 'Deshmukh',    'email' => 'arjun.d@hotmail.com',          'gender' => 'M', 'mobile' => '+91 98765 43215', 'birthdate' => '1983-09-12', 'postal_address' => '90, Indiranagar 12th Main, Bangalore 560038'],
            ['firstname' => 'Meera',    'lastname' => 'Iyer',        'email' => 'meera.iyer@gmail.com',         'gender' => 'F', 'mobile' => '+91 98765 43216', 'birthdate' => '1991-12-25', 'postal_address' => '5, Sadashivanagar, Bangalore 560080'],
            ['firstname' => 'Karthik',  'lastname' => 'Venkatesh',   'email' => 'karthik.v@gmail.com',          'gender' => 'M', 'mobile' => '+91 98765 43217', 'birthdate' => '1987-04-08', 'postal_address' => '67, JP Nagar 6th Phase, Bangalore 560078'],
            ['firstname' => 'Divya',    'lastname' => 'Nambiar',     'email' => 'divya.n@outlook.com',          'gender' => 'F', 'mobile' => '+91 98765 43218', 'birthdate' => '1993-08-19', 'postal_address' => '14, Bannerghatta Road, Bangalore 560076'],
            ['firstname' => 'Suresh',   'lastname' => 'Hegde',       'email' => 'suresh.hegde@gmail.com',       'gender' => 'M', 'mobile' => '+91 98765 43219', 'birthdate' => '1980-02-14', 'postal_address' => '33, Malleshwaram 15th Cross, Bangalore 560003'],
            ['firstname' => 'Pooja',    'lastname' => 'Shetty',      'email' => 'pooja.shetty@yahoo.com',       'gender' => 'F', 'mobile' => '+91 98765 43220', 'birthdate' => '1994-05-27', 'postal_address' => '8, Rajajinagar Industrial Town, Bangalore 560010'],
            ['firstname' => 'Amit',     'lastname' => 'Joshi',       'email' => 'amit.joshi@gmail.com',         'gender' => 'M', 'mobile' => '+91 98765 43221', 'birthdate' => '1986-10-03', 'postal_address' => '45, Electronic City Phase 1, Bangalore 560100'],
        ];

        $contactModels = [];

        foreach ($customers as $c) {
            $user = $this->createUser(
                strtolower($c['firstname']) . '_' . strtolower(substr($c['lastname'], 0, 1)),
                $c['firstname'] . ' ' . $c['lastname'],
                $c['email'],
                'password'
            );

            $contact = Contact::create([
                'user_id'        => $user->id,
                'firstname'      => $c['firstname'],
                'lastname'       => $c['lastname'],
                'email'          => $c['email'],
                'gender'         => $c['gender'],
                'mobile'         => $c['mobile'],
                'mobile_country' => 'IN',
                'birthdate'      => Carbon::parse($c['birthdate']),
                'postal_address' => $c['postal_address'],
            ]);

            $contactModels[] = $contact;
        }

        return $contactModels;
    }

    /**
     * @param Contact[] $contacts
     */
    private function subscribeCustomersToBusiness(array $contacts, Business $business): void
    {
        foreach ($contacts as $contact) {
            $business->contacts()->save($contact);
        }
    }

    private function seedVacancies(Business $business): void
    {
        $services = $business->services;
        $startDate = Carbon::tomorrow();

        for ($day = 0; $day < 14; $day++) {
            $date = $startDate->copy()->addDays($day);

            if ($date->isSunday()) {
                continue;
            }

            foreach ($services as $service) {
                $vacancy = new Vacancy([
                    'date'      => $date->toDateString(),
                    'start_at'  => $date->copy()->setTime(9, 0)->toDateTimeString(),
                    'finish_at' => $date->copy()->setTime(20, 0)->toDateTimeString(),
                    'capacity'  => rand(2, 5),
                ]);
                $vacancy->business()->associate($business);
                $vacancy->service()->associate($service);
                $vacancy->save();
            }
        }
    }

    private function seedAppointments(Business $business, User $issuer, array $contacts): void
    {
        $services = $business->services;
        $baseDate = Carbon::tomorrow();

        $appointmentData = [
            ['contact_idx' => 0, 'service_idx' => 0, 'day_offset' => 1, 'hour' => 10, 'status' => 'C', 'comment' => 'First time visit, looking forward to it!'],
            ['contact_idx' => 1, 'service_idx' => 1, 'day_offset' => 1, 'hour' => 14, 'status' => 'R', 'comment' => 'Please prepare a quiet room if possible.'],
            ['contact_idx' => 2, 'service_idx' => 0, 'day_offset' => 2, 'hour' => 11, 'status' => 'C', 'comment' => ''],
            ['contact_idx' => 3, 'service_idx' => 2, 'day_offset' => 2, 'hour' => 16, 'status' => 'R', 'comment' => 'Celebrating our anniversary!'],
            ['contact_idx' => 4, 'service_idx' => 0, 'day_offset' => 3, 'hour' => 9,  'status' => 'C', 'comment' => 'Regular customer, usual preferences.'],
            ['contact_idx' => 5, 'service_idx' => 1, 'day_offset' => 3, 'hour' => 15, 'status' => 'R', 'comment' => 'Referred by a friend.'],
        ];

        $serviceCount = $services->count();
        $contactCount = count($contacts);

        foreach ($appointmentData as $a) {
            if ($a['contact_idx'] >= $contactCount || $a['service_idx'] >= $serviceCount) {
                continue;
            }

            $contact = $contacts[$a['contact_idx']];
            $service = $services[$a['service_idx']];
            $startAt = $baseDate->copy()->addDays($a['day_offset'])->setTime($a['hour'], 0);

            $vacancy = Vacancy::where('business_id', $business->id)
                ->where('service_id', $service->id)
                ->where('date', $startAt->toDateString())
                ->first();

            if (!$vacancy) {
                continue;
            }

            $appointment = new Appointment([
                'status'   => $a['status'],
                'start_at' => $startAt,
                'finish_at'=> $startAt->copy()->addMinutes($service->duration),
                'duration' => $service->duration,
                'comments' => $a['comment'],
            ]);
            $appointment->business()->associate($business);
            $appointment->issuer()->associate($issuer);
            $appointment->contact()->associate($contact);
            $appointment->service()->associate($service);
            $appointment->vacancy()->associate($vacancy);
            $appointment->save();
        }
    }
}
