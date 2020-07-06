<?php

namespace Shaarli\Legacy;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Router
 */
class LegacyRouterTest extends TestCase
{
    /**
     * Test findPage: login page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageLoginValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_LOGIN,
            LegacyRouter::findPage('do=login', array(), false)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_LOGIN,
            LegacyRouter::findPage('do=login', array(), 1)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_LOGIN,
            LegacyRouter::findPage('do=login&stuff', array(), false)
        );
    }

    /**
     * Test findPage: login page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageLoginInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_LOGIN,
            LegacyRouter::findPage('do=login', array(), true)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_LOGIN,
            LegacyRouter::findPage('do=other', array(), false)
        );
    }

    /**
     * Test findPage: picwall page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPagePicwallValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_PICWALL,
            LegacyRouter::findPage('do=picwall', array(), false)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_PICWALL,
            LegacyRouter::findPage('do=picwall', array(), true)
        );
    }

    /**
     * Test findPage: picwall page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPagePicwallInvalid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_PICWALL,
            LegacyRouter::findPage('do=picwall&stuff', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_PICWALL,
            LegacyRouter::findPage('do=other', array(), false)
        );
    }

    /**
     * Test findPage: tagcloud page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageTagcloudValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_TAGCLOUD,
            LegacyRouter::findPage('do=tagcloud', array(), false)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_TAGCLOUD,
            LegacyRouter::findPage('do=tagcloud', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_TAGCLOUD,
            LegacyRouter::findPage('do=tagcloud&stuff', array(), false)
        );
    }

    /**
     * Test findPage: tagcloud page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageTagcloudInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_TAGCLOUD,
            LegacyRouter::findPage('do=other', array(), false)
        );
    }

    /**
     * Test findPage: linklist page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageLinklistValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_LINKLIST,
            LegacyRouter::findPage('', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_LINKLIST,
            LegacyRouter::findPage('whatever', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_LINKLIST,
            LegacyRouter::findPage('whatever', array(), false)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_LINKLIST,
            LegacyRouter::findPage('do=tools', array(), false)
        );
    }

    /**
     * Test findPage: tools page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageToolsValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_TOOLS,
            LegacyRouter::findPage('do=tools', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_TOOLS,
            LegacyRouter::findPage('do=tools&stuff', array(), true)
        );
    }

    /**
     * Test findPage: tools page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageToolsInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_TOOLS,
            LegacyRouter::findPage('do=tools', array(), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_TOOLS,
            LegacyRouter::findPage('do=tools', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_TOOLS,
            LegacyRouter::findPage('do=other', array(), true)
        );
    }

    /**
     * Test findPage: changepasswd page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageChangepasswdValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_CHANGEPASSWORD,
            LegacyRouter::findPage('do=changepasswd', array(), true)
        );
        $this->assertEquals(
            LegacyRouter::$PAGE_CHANGEPASSWORD,
            LegacyRouter::findPage('do=changepasswd&stuff', array(), true)
        );
    }

    /**
     * Test findPage: changepasswd page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageChangepasswdInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_CHANGEPASSWORD,
            LegacyRouter::findPage('do=changepasswd', array(), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_CHANGEPASSWORD,
            LegacyRouter::findPage('do=changepasswd', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_CHANGEPASSWORD,
            LegacyRouter::findPage('do=other', array(), true)
        );
    }
    /**
     * Test findPage: configure page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageConfigureValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_CONFIGURE,
            LegacyRouter::findPage('do=configure', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_CONFIGURE,
            LegacyRouter::findPage('do=configure&stuff', array(), true)
        );
    }

    /**
     * Test findPage: configure page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageConfigureInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_CONFIGURE,
            LegacyRouter::findPage('do=configure', array(), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_CONFIGURE,
            LegacyRouter::findPage('do=configure', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_CONFIGURE,
            LegacyRouter::findPage('do=other', array(), true)
        );
    }

    /**
     * Test findPage: changetag page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageChangetagValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_CHANGETAG,
            LegacyRouter::findPage('do=changetag', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_CHANGETAG,
            LegacyRouter::findPage('do=changetag&stuff', array(), true)
        );
    }

    /**
     * Test findPage: changetag page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageChangetagInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_CHANGETAG,
            LegacyRouter::findPage('do=changetag', array(), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_CHANGETAG,
            LegacyRouter::findPage('do=changetag', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_CHANGETAG,
            LegacyRouter::findPage('do=other', array(), true)
        );
    }

    /**
     * Test findPage: addlink page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageAddlinkValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_ADDLINK,
            LegacyRouter::findPage('do=addlink', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_ADDLINK,
            LegacyRouter::findPage('do=addlink&stuff', array(), true)
        );
    }

    /**
     * Test findPage: addlink page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageAddlinkInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_ADDLINK,
            LegacyRouter::findPage('do=addlink', array(), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_ADDLINK,
            LegacyRouter::findPage('do=addlink', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_ADDLINK,
            LegacyRouter::findPage('do=other', array(), true)
        );
    }

    /**
     * Test findPage: export page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageExportValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_EXPORT,
            LegacyRouter::findPage('do=export', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_EXPORT,
            LegacyRouter::findPage('do=export&stuff', array(), true)
        );
    }

    /**
     * Test findPage: export page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageExportInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_EXPORT,
            LegacyRouter::findPage('do=export', array(), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_EXPORT,
            LegacyRouter::findPage('do=export', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_EXPORT,
            LegacyRouter::findPage('do=other', array(), true)
        );
    }

    /**
     * Test findPage: import page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageImportValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_IMPORT,
            LegacyRouter::findPage('do=import', array(), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_IMPORT,
            LegacyRouter::findPage('do=import&stuff', array(), true)
        );
    }

    /**
     * Test findPage: import page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageImportInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_IMPORT,
            LegacyRouter::findPage('do=import', array(), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_IMPORT,
            LegacyRouter::findPage('do=import', array(), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_IMPORT,
            LegacyRouter::findPage('do=other', array(), true)
        );
    }

    /**
     * Test findPage: editlink page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageEditlinkValid()
    {
        $this->assertEquals(
            LegacyRouter::$PAGE_EDITLINK,
            LegacyRouter::findPage('whatever', array('edit_link' => 1), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_EDITLINK,
            LegacyRouter::findPage('', array('edit_link' => 1), true)
        );


        $this->assertEquals(
            LegacyRouter::$PAGE_EDITLINK,
            LegacyRouter::findPage('whatever', array('post' => 1), true)
        );

        $this->assertEquals(
            LegacyRouter::$PAGE_EDITLINK,
            LegacyRouter::findPage('whatever', array('post' => 1, 'edit_link' => 1), true)
        );
    }

    /**
     * Test findPage: editlink page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageEditlinkInvalid()
    {
        $this->assertNotEquals(
            LegacyRouter::$PAGE_EDITLINK,
            LegacyRouter::findPage('whatever', array('edit_link' => 1), false)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_EDITLINK,
            LegacyRouter::findPage('whatever', array('edit_link' => 1), 1)
        );

        $this->assertNotEquals(
            LegacyRouter::$PAGE_EDITLINK,
            LegacyRouter::findPage('whatever', array(), true)
        );
    }
}
