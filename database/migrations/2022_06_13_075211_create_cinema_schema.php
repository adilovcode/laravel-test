<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCinemaSchema extends Migration {
    /** ToDo: Create a migration that creates all tables for the following user stories

    For an example on how a UI for an api using this might look like, please try to book a show at https://in.bookmyshow.com/.
    To not introduce additional complexity, please consider only one cinema.

    Please list the tables that you would create including keys, foreign keys and attributes that are required by the user stories.

    ## User Stories

     **Movie exploration**
     * As a user I want to see which films can be watched and at what times
     * As a user I want to only see the shows which are not booked out

     **Show administration**
     * As a cinema owner I want to run different films at different times
     * As a cinema owner I want to run multiple films at the same time in different showrooms

     **Pricing**
     * As a cinema owner I want to get paid differently per show
     * As a cinema owner I want to give different seat types a percentage premium, for example 50 % more for vip seat

     **Seating**
     * As a user I want to book a seat
     * As a user I want to book a vip seat/couple seat/super vip/whatever
     * As a user I want to see which seats are still available
     * As a user I want to know where I'm sitting on my ticket
     * As a cinema owner I dont want to configure the seating for every show
     */
    public function up(): void {

        /**
         *
         *
         * 1. The cinema will have rooms
         * 2. Every room will have pricing range : VIP, NORMAl ...
         * 3. Every pricing will have a seats
         *
         * Administration will first configure all above only one time
         *
         *
         * 4. Cinema will also have screenings
         * 5. Screening will have connection with room and movie
         * 6. For calculating price we have to get base_price of screening and adjust the amount from room pricing
         *
         * Data fetching
         * 1. In order to fetch available seats we can:
         *      1. Get screening id and room id from input
         *      2. Fetch room join seats, left join seat_bookings with seats.id = seat_bookings.seat_id and screening_id = screenings.id
         *      3. If seat_bookings is null than the seat is available
         *
         * 2. In order to fetch with calculated amount:
         *      1. Fetch all screening group by movie
         *              movie: {}
         *              screenings: []
         *      2. Get available rooms by screening_rooms and its prices
         *      3. And adjust prices value with base_price of screening
         *
         */

        // For storing media I prefer to use laravel-media-library by spatie
        // Note that I am not using ids, and created_at, updated_at for speed

        Schema::create('cities', static function (Blueprint $table) {
            $table->string('name');
        });

        // Cinema
        Schema::create('cinemas', static function (Blueprint $table) {
            $table->string('name');
            $table->string('address');
            $table->text('details');
            $table->json('location'); // { "lon": 0, "lat": 0 }
            $table->unsignedBigInteger('city_id');

            $table->foreign('city_id')->references('id')->on('cities');
        });

        // Movie
        Schema::create('categories', static function (Blueprint $table) {
            $table->string('name');
            $table->string('parent_id');
        });

        Schema::create('movies', static function (Blueprint $table) {
            $table->string('name');
            $table->string('slug');
            $table->text('details');
            $table->date('release_date');
            $table->json('translations'); // to store them like array
            $table->bigInteger('duration'); // minutes
            $table->string('country');
            $table->json('resolutions'); // available resolutions IMAX 3D ...
        });

        Schema::create('workers', static function (Blueprint $table) {
            $table->string('name');
            $table->string('details');
            $table->string('role'); // actor, producer, writer ....
        });

        Schema::create('movie_categories', static function (Blueprint $table) {
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('category_id');

            $table->foreign('movie_id')->references('id')->on('movies');
            $table->foreign('category_id')->references('id')->on('categories');
        });

        Schema::create('movie_workers', static function (Blueprint $table) {
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('worker_id');

            $table->foreign('movie_id')->references('id')->on('movies');
            $table->foreign('worker_id')->references('id')->on('workers');
        });

        // The main logic

        // Screenings

        Schema::create('rooms', static function (Blueprint $table) {
            $table->string('name');

            //... other columns
        });

        Schema::create('room_prices', static function (Blueprint $table) {
            $table->string('name');
            $table->string('adjustment_type'); // fixed or dynamic. Fixed - fixed amount, Dynamic is percentage
            $table->string('adjustment_value');
            $table->unsignedBigInteger('room_id');

            $table->foreign('room_id')->references('id')->on('rooms');
        });

        Schema::create('seats', static function (Blueprint $table) {
            $table->string('number');
            $table->unsignedBigInteger('price_id');

            $table->foreign('price_id')->references('id')->on('room_prices');
        });

        Schema::create('screenings', static function (Blueprint $table) {
            $table->dateTime('date_time');
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('cinema_id');
            $table->string('translation');
            $table->string('resolution');
            $table->unsignedBigInteger('base_price'); // Depending on which seat was chosen this value will change

            $table->foreign('movie_id')->references('id')->on('movies');
            $table->foreign('cinema_id')->references('id')->on('cinemas');
        });

        Schema::create('screening_rooms', static function (Blueprint $table) {
            $table->unsignedBigInteger('screening_id');
            $table->unsignedBigInteger('room_id');

            $table->foreign('screening_id')->references('id')->on('screenings');
            $table->foreign('rooms')->references('id')->on('rooms');
        });

        // Booking
        Schema::create('bookings', static function (Blueprint $table) {
            //.... Some user information
        });

        Schema::create('seat_bookings', static function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('seat_id');
            $table->unsignedBigInteger('screening_id'); // -> In order to make our query faster I am duplicating this value

            $table->foreign('seat_id')->references('id')->on('seats');
            $table->foreign('screening_id')->references('id')->on('screenings');
            $table->foreign('booking_id')->references('id')->on('bookings');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
    }
}
