<?php
/**
 * Unitary tests for cleanup_url()
 */

require_once 'application/Url.php';

class CleanupUrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Clean empty UrlThanks for building nothing
     */
    public function testCleanupUrlEmpty()
    {
        $this->assertEquals('', cleanup_url(''));
    }

    /**
     * Clean an already cleaned Url
     */
    public function testCleanupUrlAlreadyClean()
    {
        $ref = 'http://domain.tld:3000';
        $this->assertEquals($ref, cleanup_url($ref));
        $ref = $ref.'/path/to/dir/';
        $this->assertEquals($ref, cleanup_url($ref));
    }

    /**
     * Clean Url needing cleaning
     */
    public function testCleanupUrlNeedClean()
    {
        $ref = 'http://domain.tld:3000';
        $this->assertEquals($ref, cleanup_url($ref.'#tk.rss_all'));
        $this->assertEquals($ref, cleanup_url($ref.'#xtor=RSS-'));
        $this->assertEquals($ref, cleanup_url($ref.'#xtor=RSS-U3ht0tkc4b'));
        $this->assertEquals($ref, cleanup_url($ref.'?action_object_map=junk'));
        $this->assertEquals($ref, cleanup_url($ref.'?action_ref_map=Cr4p!'));
        $this->assertEquals($ref, cleanup_url($ref.'?action_type_map=g4R84g3'));

        $this->assertEquals($ref, cleanup_url($ref.'?fb_stuff=v41u3'));
        $this->assertEquals($ref, cleanup_url($ref.'?fb=71m3w4573'));

        $this->assertEquals($ref, cleanup_url($ref.'?utm_campaign=zomg'));
        $this->assertEquals($ref, cleanup_url($ref.'?utm_medium=numnum'));
        $this->assertEquals($ref, cleanup_url($ref.'?utm_source=c0d3'));
        $this->assertEquals($ref, cleanup_url($ref.'?utm_term=1n4l'));

        $this->assertEquals($ref, cleanup_url($ref.'?xtor=some-url'));
        $this->assertEquals($ref, cleanup_url($ref.'?xtor=some-url&fb=som3th1ng'));
        $this->assertEquals($ref, cleanup_url(
            $ref.'?fb=stuff&utm_campaign=zomg&utm_medium=numnum&utm_source=c0d3'
        ));
        $this->assertEquals($ref, cleanup_url(
            $ref.'?xtor=some-url&fb=som3th1ng#tk.rss_all'
        ));

        // ditch annoying query params and fragment, keep useful params
        $this->assertEquals(
            $ref.'?my=stuff&is=kept',
            cleanup_url(
                $ref.'?fb=zomg&my=stuff&utm_medium=numnum&is=kept#tk.rss_all'
            )
        );

        // ditch annoying query params, keep useful params and fragment
        $this->assertEquals(
            $ref.'?my=stuff&is=kept#again',
            cleanup_url(
                $ref.'?fb=zomg&my=stuff&utm_medium=numnum&is=kept#again'
            )
        );
    }
}

