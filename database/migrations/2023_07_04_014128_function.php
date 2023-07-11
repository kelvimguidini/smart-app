<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("

        CREATE FUNCTION formatar_data_ptbr(dateValue DATE)
        RETURNS VARCHAR(255)
        BEGIN
            DECLARE monthName VARCHAR(255);
            SET monthName = MONTHNAME(dateValue);
            SET monthName = CASE
                WHEN monthName = 'January' THEN 'Jan'
                WHEN monthName = 'February' THEN 'Fev'
                WHEN monthName = 'March' THEN 'Mar'
                WHEN monthName = 'April' THEN 'Abr'
                WHEN monthName = 'May' THEN 'Mai'
                WHEN monthName = 'June' THEN 'Jun'
                WHEN monthName = 'July' THEN 'Jul'
                WHEN monthName = 'August' THEN 'Ago'
                WHEN monthName = 'September' THEN 'Set'
                WHEN monthName = 'October' THEN 'Out'
                WHEN monthName = 'November' THEN 'Nov'
                WHEN monthName = 'December' THEN 'Dez'
                ELSE monthName
            END;
            RETURN CONCAT(monthName, ' ', DATE_FORMAT(dateValue, '%Y'));
        END;
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
