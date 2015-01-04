<?php

namespace SIL\Tests;

use SIL\InterlanguageLinksLookup;
use SIL\InterlanguageLink;

use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;

use Title;

/**
 * @covers \SIL\InterlanguageLinksLookup
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class InterlanguageLinksLookupTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SIL\InterlanguageLinksLookup',
			new InterlanguageLinksLookup( $languageTargetLinksCache )
		);
	}

	public function testFindValidPageLanguageForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$verifyPropertyTypeId = function( $property ) {
			return $property->findPropertyTypeID() === '_txt';
		};

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( array( new DIWikiPage( 'Foo', NS_MAIN ) ) ) );

		$store->expects( $this->at( 1 ) )
			->method( 'getPropertyValues' )
			->with(
				$this->equalTo( new DIWikiPage( 'Foo', NS_MAIN ) ),
				$this->callback( $verifyPropertyTypeId ) )
			->will( $this->returnValue( array( new DIBlob( 'en' ), new DIBlob( 'ja' ) ) ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			'ja',
			$instance->findLastPageLanguageForTarget( $title )
		);
	}

	public function testInvalidValueToFindNoPageLanguageForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( array( new DIWikiPage( 'Foo', NS_MAIN ) ) ) );

		$store->expects( $this->at( 1 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( new DIWikiPage( 'Foo', NS_MAIN ) ) )
			->will( $this->returnValue( array( new DIWikiPage( 'invalid', NS_MAIN ) ) ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEmpty(
			$instance->findLastPageLanguageForTarget( $title )
		);
	}

	public function testFindNoPageLanguageForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( array() ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEmpty(
			$instance->findLastPageLanguageForTarget( $title )
		);
	}

	public function testFindLinkReferencesForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( array( new DIWikiPage( 'Foo', NS_MAIN ) ) ) );

		$store->expects( $this->at( 1 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( new DIWikiPage( 'Foo', NS_MAIN ) ) )
			->will( $this->returnValue( array( new DIWikiPage( 'Bar', NS_MAIN ) ) ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEquals(
			array( new DIWikiPage( 'Bar', NS_MAIN ) ),
			$instance->findLinkReferencesForTarget( $title )
		);
	}

	public function testFindNoLinkReferencesForTarget() {

		$title = Title::newFromText( __METHOD__ );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->at( 0 ) )
			->method( 'getPropertyValues' )
			->with( $this->equalTo( DIWikiPage::newFromTitle( $title ) ) )
			->will( $this->returnValue( array() ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$this->assertEmpty(
			$instance->findLinkReferencesForTarget( $title )
		);
	}

	public function testVerifyQueryStringByQueryingLanguageTargetLinks() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$verifyQueryCallback = function( $query ) {
			return $query->getQueryString() === '[[Interlanguage reference::Foo]]';
		};

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->with( $this->callback( $verifyQueryCallback ) )
			->will( $this->returnValue( $queryResult ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$instance->queryLanguageTargetLinks( $interlanguageLink );
	}

	public function testQueryLanguageTargetLinks() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$blobValue = new \SMWStringValue( '_txt' );
		$blobValue->setUserValue( 'vi' );

		$resultArray = $this->getMockBuilder( '\SMWResultArray' )
			->disableOriginalConstructor()
			->getMock();

		$resultArray->expects( $this->any() )
			->method( 'getNextDataValue' )
			->will( $this->onConsecutiveCalls( $blobValue, false )  );

		$resultArray->expects( $this->any() )
			->method( 'getResultSubject' )
			->will( $this->returnValue( new DIWikiPage( 'Bar', NS_MAIN ) ) );

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->any() )
			->method( 'getNext' )
			->will( $this->onConsecutiveCalls(
				array( $resultArray ),
				array( $resultArray ),
				false ) );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$store->expects( $this->once() )
			->method( 'getQueryResult' )
			->will( $this->returnValue( $queryResult ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$instance->queryLanguageTargetLinks( $interlanguageLink );
	}

	public function testGetLanguageTargetLinks() {

		$interlanguageLink = new InterlanguageLink( 'en', 'Foo' );

		$languageTargetLinks = array(
			'bo' => 'Bar',
			'en' => Title::newFromText( 'Foo' )
		);

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'getLanguageTargetLinksFromCache' )
			->with( $this->equalTo( $interlanguageLink ) )
			->will( $this->returnValue( $languageTargetLinks ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->tryCachedLanguageTargetLinks( $interlanguageLink );
	}

	public function testTryCachedPageLanguageForTarget() {

		$target = Title::newFromText( 'Foo' );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'getPageLanguageFromCache' )
			->with( $this->equalTo( $target ) )
			->will( $this->returnValue( 'en' ) );

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );

		$this->assertEquals(
			'en',
			$instance->tryCachedPageLanguageForTarget( $target )
		);
	}

	public function testInvalidateCachedLanguageTargetLinks() {

		$target = Title::newFromText( 'Foo' );

		$languageTargetLinksCache = $this->getMockBuilder( '\SIL\LanguageTargetLinksCache' )
			->disableOriginalConstructor()
			->getMock();

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'deleteLanguageTargetLinksFromCache' );

		$languageTargetLinksCache->expects( $this->once() )
			->method( 'deletePageLanguageForTargetFromCache' )
			->with( $this->equalTo( $target ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$instance = new InterlanguageLinksLookup( $languageTargetLinksCache );
		$instance->setStore( $store );

		$instance->doInvalidateCachedLanguageTargetLinks( $target );
	}

}