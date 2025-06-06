<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_usertours;

/**
 * Tests for tour.
 *
 * @package    tool_usertours
 * @copyright  2016 Andrew Nicols <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \tool_usertours\tour
 */
final class tour_test extends \advanced_testcase {
    /**
     * @var moodle_database
     */
    protected $db;

    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        parent::setUpBeforeClass();
    }

    /**
     * Setup to store the DB reference.
     */
    public function setUp(): void {
        global $DB;
        parent::setUp();

        $this->db = $DB;
    }

    /**
     * Tear down to restore the original DB reference.
     */
    public function tearDown(): void {
        global $DB;

        $DB = $this->db;
        parent::tearDown();
    }

    /**
     * Helper to mock the database.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function mock_database() {
        global $DB;

        $DB = $this->getMockBuilder(\moodle_database::class)
            ->getMock();

        return $DB;
    }

    /**
     * Data provider for the dirty value tester.
     *
     * @return array
     */
    public static function dirty_value_provider(): array {
        return [
            'name' => [
                'name',
                ['Lorem'],
            ],
            'description' => [
                'description',
                ['Lorem'],
            ],
            'pathmatch' => [
                'pathmatch',
                ['Lorem'],
            ],
            'enabled' => [
                'enabled',
                ['Lorem'],
            ],
            'sortorder' => [
                'sortorder',
                [1],
            ],
            'config' => [
                'config',
                ['key', 'value'],
            ],
            'showtourwhen' => [
                'showtourwhen',
                [0],
            ],
        ];
    }

    /**
     * Test that setters mark things as dirty.
     *
     * @dataProvider dirty_value_provider
     * @param   string  $name           The name of the key being tested
     * @param   mixed   $value          The value being set
     */
    public function test_dirty_values($name, $value): void {
        $tour = new \tool_usertours\tour();
        $method = 'set_' . $name;
        call_user_func_array([$tour, $method], $value);

        $rc = new \ReflectionClass(\tool_usertours\tour::class);
        $rcp = $rc->getProperty('dirty');

        $this->assertTrue($rcp->getValue($tour));
    }

    /**
     * Data provider for the get_ tests.
     *
     * @return array
     */
    public static function getter_provider(): array {
        return [
            'id' => [
                'id',
                rand(1, 100),
            ],
            'name' => [
                'name',
                'Lorem',
            ],
            'description' => [
                'description',
                'Lorem',
            ],
            'pathmatch' => [
                'pathmatch',
                'Lorem',
            ],
            'enabled' => [
                'enabled',
                'Lorem',
            ],
            'sortorder' => [
                'sortorder',
                rand(1, 100),
            ],
            'config' => [
                'config',
                ['key', 'value'],
            ],
        ];
    }

    /**
     * Test that getters return the configured value.
     *
     * @dataProvider getter_provider
     * @param   string  $key            The name of the key being tested
     * @param   mixed   $value          The value being set
     */
    public function test_getters($key, $value): void {
        $tour = new \tool_usertours\tour();

        $rc = new \ReflectionClass(tour::class);

        $rcp = $rc->getProperty($key);
        $rcp->setValue($tour, $value);

        $getter = 'get_' . $key;

        $this->assertEquals($value, $tour->$getter());
    }

    /**
     * Ensure that non-dirty tours are not persisted.
     */
    public function test_persist_non_dirty(): void {
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods(['to_record'])
            ->getMock();

        $tour->expects($this->never())
            ->method('to_record');

        $this->assertSame($tour, $tour->persist());
    }

    /**
     * Ensure that new dirty tours are persisted.
     */
    public function test_persist_dirty_new(): void {
        // Mock the database.
        $DB = $this->mock_database();

        $DB->expects($this->never())
            ->method('update_record');

        $id = rand(1, 100);
        $DB->expects($this->once())
            ->method('insert_record')
            ->willReturn($id);

        // Mock the tour.
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'to_record',
                'reload',
            ])
            ->getMock();

        $tour->expects($this->once())
            ->method('to_record');

        $tour->expects($this->once())
            ->method('reload');

        $rc = new \ReflectionClass(tour::class);

        $rcp = $rc->getProperty('dirty');
        $rcp->setValue($tour, true);

        $this->assertSame($tour, $tour->persist());

        $rcp = $rc->getProperty('id');
        $this->assertEquals($id, $rcp->getValue($tour));
    }

    /**
     * Ensure that non-dirty, forced tours are persisted.
     */
    public function test_persist_force_new(): void {
        global $DB;

        // Mock the database.
        $DB = $this->mock_database();

        $DB->expects($this->never())
            ->method('update_record');

        $id = rand(1, 100);
        $DB->expects($this->once())
            ->method('insert_record')
            ->willReturn($id);

        // Mock the tour.
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'to_record',
                'reload',
            ])
            ->getMock();

        $tour->expects($this->once())
            ->method('to_record');

        $tour->expects($this->once())
            ->method('reload');

        $this->assertSame($tour, $tour->persist(true));

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('id');
        $this->assertEquals($id, $rcp->getValue($tour));
    }

    /**
     * Ensure that dirty tours are persisted.
     */
    public function test_persist_dirty_existing(): void {
        // Mock the database.
        $DB = $this->mock_database();
        $DB->expects($this->once())
            ->method('update_record')
            ->willReturn($this->returnSelf());

        $DB->expects($this->never())
            ->method('insert_record');

        // Mock the tour.
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'to_record',
                'reload',
            ])
            ->getMock();

        $tour->expects($this->once())
            ->method('to_record');

        $tour->expects($this->once())
            ->method('reload');

        $rc = new \ReflectionClass(tour::class);

        $rcp = $rc->getProperty('id');
        $rcp->setValue($tour, 42);

        $rcp = $rc->getProperty('dirty');
        $rcp->setValue($tour, true);

        $this->assertSame($tour, $tour->persist());
    }

    /**
     * Ensure that non-dirty, forced tours are persisted.
     */
    public function test_persist_force(): void {
        global $DB;

        // Mock the database.
        $DB = $this->mock_database();

        $DB->expects($this->once())
            ->method('update_record')
            ->willReturn($this->returnSelf());

        $DB->expects($this->never())
            ->method('insert_record');

        // Mock the tour.
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'to_record',
                'reload',
            ])
            ->getMock();

        $tour->expects($this->once())
            ->method('to_record');

        $tour->expects($this->once())
            ->method('reload');

        $rc = new \ReflectionClass(tour::class);

        $rcp = $rc->getProperty('id');
        $rcp->setValue($tour, 42);

        $rcp = $rc->getProperty('dirty');
        $rcp->setValue($tour, true);

        $this->assertSame($tour, $tour->persist(true));
    }

    /**
     * Test setting config.
     */
    public function test_set_config(): void {
        $tour = new \tool_usertours\tour();

        $tour->set_config('key', 'value');
        $tour->set_config('another', [
            'foo' => 'bar',
        ]);

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('config');
        $this->assertEquals((object) [
            'key' => 'value',
            'another' => [
                'foo' => 'bar',
            ],
        ], $rcp->getValue($tour));
    }

    /**
     * Test get_config with no keys provided.
     */
    public function test_get_config_no_keys(): void {
        $tour = new \tool_usertours\tour();

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('config');

        $allvalues = (object) [
            'some' => 'value',
            'another' => 42,
            'key' => [
                'somethingelse',
            ],
        ];

        $rcp->setValue($tour, $allvalues);

        $this->assertEquals($allvalues, $tour->get_config());
    }

    /**
     * Data provider for get_config.
     *
     * @return array
     */
    public static function get_config_provider(): array {
        $allvalues = (object) [
            'some' => 'value',
            'another' => 42,
            'key' => [
                'somethingelse',
            ],
        ];

        return [
            'No nitial config' => [
                null,
                null,
                null,
                (object) [],
            ],
            'All values' => [
                $allvalues,
                null,
                null,
                $allvalues,
            ],
            'Valid string value' => [
                $allvalues,
                'some',
                null,
                'value',
            ],
            'Valid array value' => [
                $allvalues,
                'key',
                null,
                ['somethingelse'],
            ],
            'Invalid value' => [
                $allvalues,
                'notavalue',
                null,
                null,
            ],
            'Configuration value' => [
                $allvalues,
                'placement',
                null,
                \tool_usertours\configuration::get_default_value('placement'),
            ],
            'Invalid value with default' => [
                $allvalues,
                'notavalue',
                'somedefault',
                'somedefault',
            ],
        ];
    }

    /**
     * Test get_config with valid keys provided.
     *
     * @dataProvider get_config_provider
     * @param   object  $values     The config values
     * @param   string  $key        The key
     * @param   mixed   $default    The default value
     * @param   mixed   $expected   The expected value
     */
    public function test_get_config_valid_keys($values, $key, $default, $expected): void {
        $tour = new \tool_usertours\tour();

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('config');
        $rcp->setValue($tour, $values);

        $this->assertEquals($expected, $tour->get_config($key, $default));
    }

    /**
     * Check that a tour which has never been persisted is removed correctly.
     */
    public function test_remove_non_persisted(): void {
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'get_steps',
            ])
            ->getMock();

        $tour->expects($this->never())
            ->method('get_steps');

        // Mock the database.
        $DB = $this->mock_database();
        $DB->expects($this->never())
            ->method('delete_records');

        $this->assertNull($tour->remove());
    }

    /**
     * Check that a tour which has been persisted is removed correctly.
     */
    public function test_remove_persisted(): void {
        $id = rand(1, 100);

        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'get_steps',
            ])
            ->getMock();

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('id');
        $rcp->setValue($tour, $id);

        $step = $this->getMockBuilder(\tool_usertours\step::class)
            ->onlyMethods([
                'remove',
            ])
            ->getMock();

        $tour->expects($this->once())
            ->method('get_steps')
            ->willReturn([$step]);

        // Mock the database.
        $DB = $this->mock_database();

        $deleteinvocations = $this->exactly(3);
        $DB->expects($deleteinvocations)
            ->method('delete_records')
            ->willReturnCallback(function ($table, $conditions) use ($deleteinvocations, $id) {
                switch (self::getInvocationCount($deleteinvocations)) {
                    case 1:
                        $this->assertEquals('tool_usertours_tours', $table);
                        $this->assertEquals(['id' => $id], $conditions);
                        return null;
                        break;
                    case 2:
                        $this->assertEquals('user_preferences', $table);
                        $this->assertEquals(['name' => tour::TOUR_LAST_COMPLETED_BY_USER . $id], $conditions);
                        return null;
                        break;
                    case 3:
                        $this->assertEquals('user_preferences', $table);
                        $this->assertEquals(['name' => tour::TOUR_REQUESTED_BY_USER . $id], $conditions);
                        return null;
                        break;
                    default:
                        $this->fail('Unexpected call to delete_records');
                }
            });

        $DB->expects($this->once())
            ->method('get_records')
            ->with($this->equalTo('tool_usertours_tours'), $this->equalTo(null))
            ->willReturn([]);

        $this->assertNull($tour->remove());
    }

    /**
     * Teset that sortorder is reset according to sortorder with values from 0.
     */
    public function test_reset_step_sortorder(): void {
        $tour = new \tool_usertours\tour();

        $mockdata = [];
        for ($i = 4; $i >= 0; $i--) {
            $id = rand($i * 10, ($i * 10) + 9);
            $mockdata[] = (object) ['id' => $id];
            $expectations[] = [4 - $i, ['id' => $id]];
        }

        // Mock the database.
        $DB = $this->mock_database();
        $DB->expects($this->once())
            ->method('get_records')
            ->willReturn($mockdata);

        $setfieldinvocations = $this->exactly(5);
        $DB->expects($setfieldinvocations)
            ->method('set_field')
            ->willReturnCallback(function ($table, $field, $value, $conditions) use (
                $setfieldinvocations,
                $expectations,
            ): void {
                $expectation = $expectations[self::getInvocationCount($setfieldinvocations) - 1];
                $this->assertEquals('tool_usertours_steps', $table);
                $this->assertEquals('sortorder', $field);
                $this->assertEquals($expectation[0], $value);
                $this->assertEquals($expectation[1], $conditions);
            });

        $tour->reset_step_sortorder();
    }

    /**
     * Test that a disabled tour should never be shown to users.
     */
    public function test_should_show_for_user_disabled(): void {
        $tour = new \tool_usertours\tour();
        $tour->set_enabled(false);

        $this->assertFalse($tour->should_show_for_user());
    }

    /**
     * Provider for should_show_for_user.
     *
     * @return array
     */
    public static function should_show_for_user_provider(): array {
        $time = time();
        return [
            'Not seen by user at all' => [
                null,
                null,
                null,
                [],
                true,
            ],
            'Completed by user before majorupdatetime' => [
                $time - DAYSECS,
                null,
                $time,
                [],
                true,
            ],
            'Completed by user since majorupdatetime' => [
                $time,
                null,
                $time - DAYSECS,
                [],
                false,
            ],
            'Requested by user before current completion' => [
                $time,
                $time - DAYSECS,
                $time - MINSECS,
                [],
                false,
            ],
            'Requested by user since completion' => [
                $time - DAYSECS,
                $time,
                'null',
                [],
                true,
            ],
            'Tour will show on each load' => [
                $time,
                $time - DAYSECS,
                null,
                [
                    'showtourwhen' => tour::SHOW_TOUR_ON_EACH_PAGE_VISIT,
                ],
                true,
            ],
        ];
    }

    /**
     * Test that a disabled tour should never be shown to users.
     *
     * @dataProvider should_show_for_user_provider
     * @param   mixed   $completiondate The user's completion date for this tour
     * @param   mixed   $requesteddate  The user's last requested date for this tour
     * @param   mixed   $updateddate    The date this tour was last updated
     * @param   mixed   $config         The tour config to apply
     * @param   string  $expectation    The expected tour key
     */
    public function test_should_show_for_user(
        $completiondate,
        $requesteddate,
        $updateddate,
        $config,
        $expectation,
    ): void {
        // Uses user preferences so we must be in a user context.
        $this->resetAfterTest();
        $this->setAdminUser();

        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'get_id',
                'is_enabled',
            ])
            ->getMock();

        $tour->method('is_enabled')
            ->willReturn(true);

        foreach ($config as $key => $value) {
            $tour->set_config($key, $value);
        }

        $id = rand(1, 100);
        $tour->method('get_id')
            ->willReturn($id);

        if ($completiondate !== null) {
            set_user_preference(\tool_usertours\tour::TOUR_LAST_COMPLETED_BY_USER . $id, $completiondate);
        }

        if ($requesteddate !== null) {
            set_user_preference(\tool_usertours\tour::TOUR_REQUESTED_BY_USER . $id, $requesteddate);
        }

        if ($updateddate !== null) {
            $tour->set_config('majorupdatetime', $updateddate);
        }

        $this->assertEquals($expectation, $tour->should_show_for_user());
    }

    /**
     * Provider for get_tour_key.
     *
     * @return array
     */
    public static function get_tour_key_provider(): array {
        $id = rand(1, 100);
        $time = time();

        return [
            'No initial values' => [
                $id,
                [null, $time],
                static::logicalOr(
                    new \PHPUnit\Framework\Constraint\IsEqual($time),
                    new \PHPUnit\Framework\Constraint\GreaterThan($time),
                ),
                true,
                null,
                sprintf('tool_usertours_\d_%d_%s', $id, $time),
            ],

            'Initial tour time, no user pref' => [
                $id,
                [$time],
                null,
                false,
                null,
                sprintf('tool_usertours_\d_%d_%s', $id, $time),
            ],
            'Initial tour time, with user reset lower' => [
                $id,
                [$time],
                null,
                false,
                $time - DAYSECS,
                sprintf('tool_usertours_\d_%d_%s', $id, $time),
            ],
            'Initial tour time, with user reset higher' => [
                $id,
                [$time],
                null,
                false,
                $time + DAYSECS,
                sprintf('tool_usertours_\d_%d_%s', $id, $time + DAYSECS),
            ],
        ];
    }

    /**
     * Test that get_tour_key provides the anticipated unique keys.
     *
     * @dataProvider get_tour_key_provider
     * @param   int     $id             The tour ID
     * @param   array   $getconfig      The mocked values for get_config calls
     * @param   array   $setconfig      The mocked values for set_config calls
     * @param   bool    $willpersist    Whether a persist is expected
     * @param   mixed   $userpref       The value to set for the user preference
     * @param   string  $expectation    The expected tour key
     */
    public function test_get_tour_key($id, $getconfig, $setconfig, $willpersist, $userpref, $expectation): void {
        // Uses user preferences so we must be in a user context.
        $this->resetAfterTest();
        $this->setAdminUser();

        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'get_config',
                'set_config',
                'get_id',
                'persist',
            ])
            ->getMock();

        if ($getconfig) {
            $getinvocations = $this->exactly(count($getconfig));
            $tour->expects($getinvocations)
                ->method('get_config')
                ->willReturnCallback(function () use ($getinvocations, $getconfig) {
                    return $getconfig[self::getInvocationCount($getinvocations) - 1];
                });
        }

        if ($setconfig) {
            $tour->expects($this->once())
                ->method('set_config')
                ->with($this->equalTo('majorupdatetime'), $setconfig)
                ->will($this->returnSelf());
        } else {
            $tour->expects($this->never())
                ->method('set_config');
        }

        if ($willpersist) {
            $tour->expects($this->once())
                ->method('persist');
        } else {
            $tour->expects($this->never())
                ->method('persist');
        }

        $tour->expects($this->any())
            ->method('get_id')
            ->willReturn($id);

        if ($userpref !== null) {
            set_user_preference(\tool_usertours\tour::TOUR_REQUESTED_BY_USER . $id, $userpref);
        }

        $this->assertMatchesRegularExpression(
            '/' . $expectation . '/',
            $tour->get_tour_key()
        );
    }

    /**
     * Ensure that the request_user_reset function sets an appropriate value for the tour.
     */
    public function test_requested_user_reset(): void {
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'get_id',
            ])
            ->getMock();

        $id = rand(1, 100);
        $time = time();

        $tour->expects($this->once())
            ->method('get_id')
            ->willReturn($id);

        $tour->request_user_reset();

        $this->assertGreaterThanOrEqual($time, get_user_preferences(\tool_usertours\tour::TOUR_REQUESTED_BY_USER . $id));
    }

    /**
     * Ensure that the request_user_reset function sets an appropriate value for the tour.
     */
    public function test_mark_user_completed(): void {
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods([
                'get_id',
            ])
            ->getMock();

        $id = rand(1, 100);
        $time = time();

        $tour->expects($this->once())
            ->method('get_id')
            ->willReturn($id);

        $tour->mark_user_completed();

        $this->assertGreaterThanOrEqual($time, get_user_preferences(\tool_usertours\tour::TOUR_LAST_COMPLETED_BY_USER . $id));
    }

    /**
     * Provider for the is_first_tour and is_last_tour tests.
     *
     * @return array
     */
    public static function sortorder_first_last_provider(): array {
        $topcount = rand(10, 100);
        return [
            'Only tour => first + last' => [
                0,
                true,
                1,
                true,
            ],
            'First tour of many' => [
                0,
                true,
                $topcount,
                false,
            ],
            'Last tour of many' => [
                $topcount - 1,
                false,
                $topcount,
                true,
            ],
            'Middle tour of many' => [
                5,
                false,
                $topcount,
                false,
            ],
        ];
    }

    /**
     * Test the is_first_tour() function.
     *
     * @dataProvider sortorder_first_last_provider
     * @param   int     $sortorder      The new sort order
     * @param   bool    $isfirst        Whether this is the first tour
     * @param   int     $total          The number of tours
     * @param   bool    $islast         Whether this is the last tour
     */
    public function test_is_first_tour($sortorder, $isfirst, $total, $islast): void {
        $tour = new \tool_usertours\tour();

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('sortorder');
        $rcp->setValue($tour, $sortorder);

        $this->assertEquals($isfirst, $tour->is_first_tour());
    }

    /**
     * Test the is_last_tour() function.
     *
     * @dataProvider sortorder_first_last_provider
     * @param   int     $sortorder      The new sort order
     * @param   bool    $isfirst        Whether this is the first tour
     * @param   int     $total          The number of tours
     * @param   bool    $islast         Whether this is the last tour
     */
    public function test_is_last_tour_calculated($sortorder, $isfirst, $total, $islast): void {
        $tour = new \tool_usertours\tour();

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('sortorder');
        $rcp->setValue($tour, $sortorder);

        // The total will be calculated.
        $DB = $this->mock_database();
        $DB->expects($this->once())
            ->method('count_records')
            ->willReturn($total);
        $this->assertEquals($islast, $tour->is_last_tour());
    }

    /**
     * Test the is_last_tour() function.
     *
     * @dataProvider sortorder_first_last_provider
     * @param   int     $sortorder      The new sort order
     * @param   bool    $isfirst        Whether this is the first tour
     * @param   int     $total          The number of tours
     * @param   bool    $islast         Whether this is the last tour
     */
    public function test_is_last_tour_provided($sortorder, $isfirst, $total, $islast): void {
        $tour = new \tool_usertours\tour();

        $rc = new \ReflectionClass(tour::class);
        $rcp = $rc->getProperty('sortorder');
        $rcp->setValue($tour, $sortorder);

        // The total is provided.
        // No DB calls expected.
        $DB = $this->mock_database();
        $DB->expects($this->never())
            ->method('count_records')
            ->willReturn(0);
        $this->assertEquals($islast, $tour->is_last_tour($total));
    }

    /**
     * Data provider for the get_filter_values tests.
     *
     * @return array
     */
    public static function get_filter_values_provider(): array {
        $cheese = ['cheddar', 'boursin', 'mozzarella'];
        $horses = ['coolie', 'dakota', 'leo', 'twiggy'];
        return [
            'No config' => [
                [],
                'cheese',
                [],
            ],
            'Some config for another filter' => [
                [
                    'horses' => $horses,
                ],
                'cheese',
                [],
            ],
            'Some config for this filter' => [
                [
                    'horses' => $horses,
                ],
                'horses',
                $horses,
            ],
            'Some config for several filters' => [
                [
                    'horses' => $horses,
                    'cheese' => $cheese,
                ],
                'horses',
                $horses,
            ],
        ];
    }

    /**
     * Tests for the get_filter_values function.
     *
     * @dataProvider get_filter_values_provider
     * @param   array       $fullconfig     The config value being tested
     * @param   string      $filtername     The name of the filter being tested
     * @param   array       $expectedvalues The expected result
     */
    public function test_get_filter_values($fullconfig, $filtername, $expectedvalues): void {
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods(['get_config'])
            ->getMock();

        $tour->expects($this->once())
            ->method('get_config')
            ->will($this->returnValue($fullconfig));

        $this->assertEquals($expectedvalues, $tour->get_filter_values($filtername));
    }

    /**
     * Data provider for set_filter_values tests.
     *
     * @return  array
     */
    public static function set_filter_values_provider(): array {
        $cheese = ['cheddar', 'boursin', 'mozzarella'];
        $horses = ['coolie', 'dakota', 'leo', 'twiggy'];

        return [
            'No initial value' => [
                [],
                'cheese',
                $cheese,
                ['cheese' => $cheese],
            ],
            'Existing filter merged' => [
                ['horses' => $horses],
                'cheese',
                $cheese,
                ['horses' => $horses, 'cheese' => $cheese],
            ],
            'Existing filter updated' => [
                ['cheese' => $cheese],
                'cheese',
                ['cheddar'],
                ['cheese' => ['cheddar']],
            ],
            'Existing filter updated with merge' => [
                ['horses' => $horses, 'cheese' => $cheese],
                'cheese',
                ['cheddar'],
                ['horses' => $horses, 'cheese' => ['cheddar']],
            ],
        ];
    }

    /**
     * Base tests for set_filter_values.
     *
     * @dataProvider set_filter_values_provider
     * @param   array       $currentvalues  The current value
     * @param   string      $filtername     The name of the filter to add to
     * @param   array       $newvalues      The new values to store
     * @param   array       $expectedvalues The combined values
     */
    public function test_set_filter_values_merge($currentvalues, $filtername, $newvalues, $expectedvalues): void {
        $tour = $this->getMockBuilder(tour::class)
            ->onlyMethods(['get_config', 'set_config'])
            ->getMock();

        $tour->expects($this->once())
            ->method('get_config')
            ->will($this->returnValue($currentvalues));

        $tour->expects($this->once())
            ->method('set_config')
            ->with(
                $this->equalTo('filtervalues'),
                $this->equalTo($expectedvalues)
            );

        $tour->set_filter_values($filtername, $newvalues);
    }
}
