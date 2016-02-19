<?php
namespace Glow\Database\Schema\Blueprint;

trait WithEmptyTimestamps
{

    /**
     * Add creation and update timestamps to the table with 0 default value (for mysql < 5.7).
     *
     * @return void
     */
    public function emptyTimestamps() {
        $this->timestamp('created_at')->default('0000-00-00 00:00:00');

        $this->timestamp('updated_at')->default('0000-00-00 00:00:00');
    }
}