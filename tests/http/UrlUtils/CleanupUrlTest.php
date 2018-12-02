<?php
/**
 * Unitary tests for cleanup_url()
 */

namespace Shaarli\Http;

require_once 'application/http/UrlUtils.php';

class CleanupUrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string reference URL
     */
    protected $ref = 'http://domain.tld:3000';


    /**
     * Clean empty URL
     */
    public function testCleanupUrlEmpty()
    {
        $this->assertEquals('', cleanup_url(''));
    }

    /**
     * Clean an already cleaned URL
     */
    public function testCleanupUrlAlreadyClean()
    {
        $this->assertEquals($this->ref, cleanup_url($this->ref));
        $this->ref2 = $this->ref.'/path/to/dir/';
        $this->assertEquals($this->ref2, cleanup_url($this->ref2));
    }

    /**
     * Clean URL fragments
     */
    public function testCleanupUrlFragment()
    {
        $this->assertEquals($this->ref, cleanup_url($this->ref.'#tk.rss_all'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'#xtor=RSS-'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'#xtor=RSS-U3ht0tkc4b'));
    }

    /**
     * Clean URL query - single annoying parameter
     */
    public function testCleanupUrlQuerySingle()
    {
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?action_object_map=junk'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?action_ref_map=Cr4p!'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?action_type_map=g4R84g3'));

        $this->assertEquals($this->ref, cleanup_url($this->ref.'?fb_stuff=v41u3'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?fb=71m3w4573'));

        $this->assertEquals($this->ref, cleanup_url($this->ref.'?utm_campaign=zomg'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?utm_medium=numnum'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?utm_source=c0d3'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?utm_term=1n4l'));

        $this->assertEquals($this->ref, cleanup_url($this->ref.'?xtor=some-url'));

        $this->assertEquals($this->ref, cleanup_url($this->ref.'?campaign_name=junk'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?campaign_start=junk'));
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?campaign_item_index=junk'));
    }

    /**
     * Clean URL query - multiple annoying parameters
     */
    public function testCleanupUrlQueryMultiple()
    {
        $this->assertEquals($this->ref, cleanup_url($this->ref.'?xtor=some-url&fb=som3th1ng'));

        $this->assertEquals($this->ref, cleanup_url(
            $this->ref.'?fb=stuff&utm_campaign=zomg&utm_medium=numnum&utm_source=c0d3'
        ));

        $this->assertEquals($this->ref, cleanup_url(
            $this->ref.'?campaign_start=zomg&campaign_name=numnum'
        ));
    }

    /**
     * Clean URL query - multiple annoying parameters and fragment
     */
    public function testCleanupUrlQueryFragment()
    {
        $this->assertEquals($this->ref, cleanup_url(
            $this->ref.'?xtor=some-url&fb=som3th1ng#tk.rss_all'
        ));

        // ditch annoying query params and fragment, keep useful params
        $this->assertEquals(
            $this->ref.'?my=stuff&is=kept',
            cleanup_url(
                $this->ref.'?fb=zomg&my=stuff&utm_medium=numnum&is=kept#tk.rss_all'
            )
        );

        // ditch annoying query params, keep useful params and fragment
        $this->assertEquals(
            $this->ref.'?my=stuff&is=kept#again',
            cleanup_url(
                $this->ref.'?fb=zomg&my=stuff&utm_medium=numnum&is=kept#again'
            )
        );
    }
}
