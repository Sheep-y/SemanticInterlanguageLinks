<?php

namespace SIL;

use SMW\ParserData;
use SMW\Subobject;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LanguageLinkAnnotator {

	/**
	 * @var ParserData
	 */
	private $parserData;

	/**
	 * @since 1.0
	 *
	 * @param ParserData $parserData
	 */
	public function __construct( ParserData $parserData ) {
		$this->parserData = $parserData;
	}

	/**
	 * @since 1.0
	 *
	 * @param InterlanguageLink $interlanguageLink
	 */
	public function addAnnotationForInterlanguageLink( InterlanguageLink $interlanguageLink ) {

		$subobject = new Subobject( $this->parserData->getTitle() );
		$subobject->setEmptyContainerForId( $interlanguageLink->getContainerId() );

		$subobject->getSemanticData()->addDataValue(
			$interlanguageLink->newLanguageDataValue()
		);

		$subobject->getSemanticData()->addDataValue(
			$interlanguageLink->newLinkReferenceDataValue()
		);

		$this->parserData->getSemanticData()->addPropertyObjectValue(
			$interlanguageLink->newContainerProperty(),
			$subobject->getContainer()
		);

		$this->parserData->pushSemanticDataToParserOutput();
		$this->parserData->setSemanticDataStateToParserOutputProperty();
	}

	/**
	 * @since 1.0
	 *
	 * @param InterwikiLanguageLink $interwikiLanguageLink
	 */
	public function addAnnotationForInterwikiLanguageLink( InterwikiLanguageLink $interwikiLanguageLink ) {

		$subobject = new Subobject( $this->parserData->getTitle() );
		$subobject->setEmptyContainerForId( $interwikiLanguageLink->getContainerId() );

		$subobject->getSemanticData()->addDataValue(
			$interwikiLanguageLink->newLanguageDataValue()
		);

		$subobject->getSemanticData()->addDataValue(
			$interwikiLanguageLink->newInterwikiReferenceDataValue()
		);

		$this->parserData->getSemanticData()->addPropertyObjectValue(
			$interwikiLanguageLink->newContainerProperty(),
			$subobject->getContainer()
		);

		$this->parserData->pushSemanticDataToParserOutput();
		$this->parserData->setSemanticDataStateToParserOutputProperty();
	}

}