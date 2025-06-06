@block @block_section_links
Feature: The section links block allows users to quickly navigate around a moodle course
  In order to navigate a moodle course
  As a teacher
  I can use the section links block

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | numsections | coursedisplay |
      | Course 1 | C1        | 0        | 20          | 1             |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name              | intro          | course | section | idnumber | assignsubmission_file_enabled |
      | assign     | Test assignment 1 | Offline text   | C1     | 5       | assign1  | 0                             |
    And the following config values are set as admin:
      | showsectionname | 0 | block_section_links |
      | unaddableblocks |   | theme_boost         |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: Add the section links block to a course.
    Given I add the "Section links" block
    And I turn editing mode off
    And I should see "5" in the "Section links" "block"
    When I follow "5"
    Then I should see "Test assignment 1"

  Scenario: Add the section links block to a course and limit the sections displayed.
    Given I add the "Section links" block
    And I configure the "Section links" block
    And I set the following fields to these values:
      | config_numsections1 | 5 |
      | config_incby1 | 5 |
      | config_numsections2 | 40 |
      | config_incby2 | 10 |
    And I press "Save changes"
    And I turn editing mode off
    And I should see "5" in the "Section links" "block"
    When I follow "5"
    Then I should see "Test assignment 1"

  Scenario: Add the section links block to a course and limit the sections displayed using the alternative number of sections.
    Given I add the "Section links" block
    And I configure the "Section links" block
    And I set the following fields to these values:
      | config_numsections1 | 5 |
      | config_incby1 | 1 |
      | config_numsections2 | 10 |
      | config_incby2 | 5 |
    And I press "Save changes"
    And I turn editing mode off
    And I should see "5" in the "Section links" "block"
    When I follow "5"
    Then I should see "Test assignment 1"

  Scenario: Subsections numbers are not displayed in the Section links block
    Given the following "activity" exists:
      | activity | subsection  |
      | name     | Subsection1 |
      | course   | C1          |
      | idnumber | subsection1 |
      | section  | 1           |
    And the following "blocks" exist:
      | blockname     | contextlevel | reference | pagetypepattern | defaultregion |
      | section_links | Course       | C1        | course-view-*   | side-pre      |
    When I am on "Course 1" course homepage
    Then "21" "link" should not exist in the "Section links" "block"
