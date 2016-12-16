<?php

namespace Drupal\sharerich\Tests;

use \Drupal\simpletest\WebTestBase;
use \Drupal\node\Entity\Node;
use \Drupal\node\Entity\NodeType;
/**
 * Sharerich tests.
 *
 * @group sharerich
 */
class SharerichTests extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block', 'node', 'field', 'text', 'sharerich');

  /**
   * A user with the 'Administer sharerich' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * List of services to check.
   */
  protected $services;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Services to test.
    $this->services = ['facebook', 'email', 'tumblr', 'twitter'];

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser(array(
      'access administration pages',
      'administer sharerich'
    ), 'Sharerich Admin', TRUE); //@todo remove TRUE
  }

  function testLinkToConfig() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/modules');
    $link = $this->xpath('//a[contains(@href, :href) and contains(@id, :id)]', [
      ':href' => 'admin/structure/sharerich',
      ':id' => 'edit-modules-sharing-sharerich-links-configure'
    ]);
    $this->assertTrue(count($link) === 1, 'Link to config is present');
  }

  /**
   * Admin UI.
   */
  function testAdminUI() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/sharerich/default');

    // Test that the imported set is correct.
    $element = $this->xpath('//input[@type="text" and @id="edit-label" and @value="Default"]');
    $this->assertTrue(count($element) === 1, 'The label is correct.');
;
    foreach ($this->services as $item) {
      // Assert that the checkboxes are ticked.
      $element = $this->xpath('//input[@type="checkbox" and @name="services[' . $item . '][enabled]" and @checked="checked"]');
      $this->assertTrue(count($element) === 1, t('The :item is checked.', array(':item' => ucfirst($item))));

      $actual = (string) $this->xpath('//textarea[@name="services[' . $item . '][markup]"]')[0];
      $expected = (string) $this->xpath('//input[@type="hidden"][@name="services[' . $item . '][default_markup]"]/@value')[0];
      // Normalize strings.
      $actual=preg_replace('/(\r\n|\r|\n|\s|\t)/s'," ",$actual);
      $expected=preg_replace('/(\r\n|\r|\n|\s|\t)/s'," ",$expected);
      $this->assertTrue($actual == $expected, t('The :item widget is correct.', array(':item' => ucfirst($item))));
    }
  }

  /**
   * Test sharerich block.
   */
  function testBlock() {
    $this->drupalLogin($this->adminUser);

    // Create content type.
    $node_type = NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
    ]);
    $node_type->save();

    // Create page.
    $page = Node::create([
      'type' => 'page',
      'title' => 'Sharerich page',
    ]);
    $page->save();

    // Visit node.
    $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $page->id()]);
    $this->drupalGet($url->toString());

    $text = $this->xpath('//div[@id="block-sharerich-block"]/h2/text()')[0][0];
    $this->assertEqual($text, t('Share this'), 'The title of sharerich block is correct');

    $element = $this->xpath('//div[contains(@class, "sharerich-wrapper") and contains(@class, "sharerich-vertical") and contains(@class, "sharerich-sticky")]');
    $this->assertTrue(!empty($element), 'Found a sticky sharerich block');

    foreach ($this->services as $item) {
      $text = $this->xpath('//div[@id="block-sharerich-block"]//ul/li[@class="rrssb-' . $item . '"]//span[@class="rrssb-text"]/text()')[0][0];
      $this->assertEqual($text, $item, t('The text of :item button is correct', array(':item' => $item)));
    }
  }
}
