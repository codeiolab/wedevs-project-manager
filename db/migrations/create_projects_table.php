<?php

use Illuminate\Database\Capsule\Manager as Capsule;

use CPM\Core\Database\Abstract_Migration as Migration;

class Create_Projects_Table extends Migration {
    public function schema() {
        Capsule::schema()->create( 'cpm_projects', function( $table ) {
            $table->increments( 'id' );
            $table->string( 'projectable_type' )->nullable();
            $table->string('title');
            $table->text('des')->nullable();
            $table->tinyInteger( 'status' )->default(1);
            $table->float( 'budget' )->nullable();
            $table->float( 'pay_rate' )->nullable();
            $table->timestamp( 'est_completion' )->nullable();
            $table->string( 'color_code' )->nullable();
            $table->tinyInteger( 'order' )->nullable();
            $table->unsignedInteger( 'created_by' );
            $table->unsignedInteger( 'updated_by' );
            $table->timestamps();
        });
    }
}