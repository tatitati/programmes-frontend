<?php
declare(strict_types = 1);

namespace App\DsShared\Helpers\Enumeration;

class GuidanceWarningEnum
{
    /**
     * Guidance warning codes
     */

    // Language
    public const L1 = 'L1';
    public const L2 = 'L2';
    public const L3 = 'L3';
    public const CODES_LANGUAGE = [self::L1, self::L2, self::L3];

    // Adult Humour
    public const LA = 'LA';
    public const CODES_HUMOUR = [self::LA];

    // Sex
    public const S1 = 'S1';
    public const S2 = 'S2';
    public const S3 = 'S3';
    public const CODES_SEX = [self::S1, self::S2, self::S3];

    // Violence
    public const V1 = 'V1';
    public const V2 = 'V2';
    public const V3 = 'V3';
    public const V4 = 'V4';
    public const CODES_VIOLENCE = [self::V1, self::V2, self::V3, self::V4];

    // Disturbing
    public const D1 = 'D1';
    public const D2 = 'D2';
    public const D3 = 'D3';
    public const CODES_DISTURBING = [self::D1, self::D2, self::D3];

    // Flashing images
    public const RFI = 'RFI';
    public const CODES_FLASHING = [self::RFI];

    // Watershed
    public const WV = 'WV';
    public const WL = 'WL';
    public const WD = 'WD';
    public const W1 = 'W1';
    public const W2 = 'W2';
    public const W3 = 'W3';
    public const W4 = 'W4';
    public const W5 = 'W5';
    public const W6 = 'W6';
    public const W7 = 'W7';
    public const W8 = 'W8';
    public const CODES_WATERSHED = [self::WV, self::WL, self::WD, self::W1, self::W2, self::W3, self::W4, self::W5, self::W6, self::W7, self::W8];

    /**
     * Guidance warning text length
     */

    public const TEXT_SHORT = 'SHORT';
    public const TEXT_LONG = 'LONG';

    /**
     * Guidance warning short text
     */

    // Language
    public const L1_SHORT = 'some strong language';
    public const L2_SHORT = 'strong language';
    public const L3_SHORT = 'very strong language';
    // Adult Humour
    public const LA_SHORT = 'adult humour';
    // Sex
    public const S1_SHORT = 'some sexual content';
    public const S2_SHORT = 'sexual content';
    public const S3_SHORT = 'explicit sexual content';
    // Violence
    public const V1_SHORT = 'some violence';
    public const V2_SHORT = 'prolonged violence';
    public const V3_SHORT = 'graphic violence';
    public const V4_SHORT = 'sexual violence';
    // Disturbing
    public const D1_SHORT = 'some upsetting scenes';
    public const D2_SHORT = 'upsetting scenes';
    public const D3_SHORT = 'disturbing scenes';
    // Flashing images
    public const RFI_SHORT = 'flashing images';
    // Watershed
    public const WV_SHORT = 'moderate violence';
    public const WL_SHORT = 'language which may offend';
    public const WD_SHORT = 'some upsetting scenes';
    public const W1_SHORT = 'adult humour';
    public const W2_SHORT = 'adult themes';
    public const W3_SHORT = 'some nudity';
    public const W4_SHORT = 'scenes of drug use';
    public const W5_SHORT = 'graphic drug use';
    public const W6_SHORT = 'behaviour which could be imitated';
    public const W7_SHORT = 'graphic medical scenes';
    public const W8_SHORT = 'horror';

    /**
     * Guidance warning LONG text
     */

    // Language
    public const L1_LONG = self::L1_SHORT;
    public const L2_LONG = self::L2_SHORT;
    public const L3_LONG = self::L3_SHORT;
    // Adult Humour
    public const LA_LONG = self::LA_SHORT;
    // Sex
    public const S1_LONG = 'some scenes of a sexual nature';
    public const S2_LONG = 'scenes of a sexual nature';
    public const S3_LONG = 'explicit sexual scenes';
    // Violence
    public const V1_LONG = 'some violent scenes';
    public const V2_LONG = 'prolonged violent scenes';
    public const V3_LONG = 'graphic violent scenes';
    public const V4_LONG = 'scenes of sexual violence';
    // Disturbing
    public const D1_LONG = 'some scenes which some viewers may find upsetting';
    public const D2_LONG = 'scenes which some viewers may find upsetting';
    public const D3_LONG = 'scenes which some viewers may find disturbing';
    // Flashing images
    public const RFI_LONG = 'scenes of flashing images';
    // Watershed
    public const WV_LONG = 'scenes of moderate violence';
    public const WL_LONG = 'language which some may find offensive';
    public const WD_LONG = 'some scenes which some viewers may find upsetting';
    public const W1_LONG = self::W1_SHORT;
    public const W2_LONG = 'with adult themes';
    public const W3_LONG = self::W3_SHORT;
    public const W4_LONG = self::W4_SHORT;
    public const W5_LONG = 'graphic scenes of drug use';
    public const W6_LONG = self::W6_SHORT;
    public const W7_LONG = self::W7_SHORT;
    public const W8_LONG = self::W8_SHORT;
}
