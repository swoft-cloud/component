<?php
declare(strict_types=1);

namespace SwoftTest\Validator\Testing\Validator;

use Swoft\Validator\Annotation\Mapping\AfterDate;
use Swoft\Validator\Annotation\Mapping\Alpha;
use Swoft\Validator\Annotation\Mapping\AlphaDash;
use Swoft\Validator\Annotation\Mapping\AlphaNum;
use Swoft\Validator\Annotation\Mapping\BeforeDate;
use Swoft\Validator\Annotation\Mapping\Chs;
use Swoft\Validator\Annotation\Mapping\ChsAlpha;
use Swoft\Validator\Annotation\Mapping\ChsAlphaDash;
use Swoft\Validator\Annotation\Mapping\ChsAlphaNum;
use Swoft\Validator\Annotation\Mapping\Confirm;
use Swoft\Validator\Annotation\Mapping\Date;
use Swoft\Validator\Annotation\Mapping\DateRange;
use Swoft\Validator\Annotation\Mapping\Dns;
use Swoft\Validator\Annotation\Mapping\FileMediaType;
use Swoft\Validator\Annotation\Mapping\FileSize;
use Swoft\Validator\Annotation\Mapping\FileSuffix;
use Swoft\Validator\Annotation\Mapping\IsFile;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Low;
use Swoft\Validator\Annotation\Mapping\NotInEnum;
use Swoft\Validator\Annotation\Mapping\NotInRange;
use Swoft\Validator\Annotation\Mapping\Upper;
use Swoft\Validator\Annotation\Mapping\Url;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class TestRule
 *
 * @since 2.0
 *
 * @Validator(name="testRule")
 */
class TestRule
{
    /**
     * @IsString()
     * @AfterDate(date="2019-07-08")
     */
    protected $dataAfterDate;

    /**
     * @IsString()
     * @Alpha(message="alpha message")
     */
    protected $dataAlpha;

    /**
     * @IsString()
     * @AlphaDash(message="alphadash message")
     */
    protected $dataAlphaDash;

    /**
     * @IsString()
     * @AlphaNum(message="alphanum message")
     */
    protected $dataAlphaNum;

    /**
     * @IsString()
     * @BeforeDate(date="2019-07-08",message="before date message")
     */
    protected $dataBeforeDate;

    /**
     * @IsString()
     * @Chs(message="chs message")
     */
    protected $dataChs;

    /**
     * @IsString()
     * @ChsAlpha(message="chsalpha message")
     */
    protected $dataChsAlpha;

    /**
     * @IsString()
     * @ChsAlphaDash(message="chsalphadash message")
     */
    protected $dataChsAlphaDash;

    /**
     * @IsString()
     * @ChsAlphaNum(message="chsalphanum message")
     */
    protected $dataChsAlphaNum;

    /**
     * @IsString()
     * @Confirm(name="confirm",message="confirm message")
     */
    protected $dataConfirm;

    /**
     * @IsString()
     */
    protected $confirm;

    /**
     * @IsString()
     * @Date(message="date message")
     */
    protected $dataDate;

    /**
     * @IsString()
     * @DateRange(start="2019-07-01",end="2019-07-08",message="daterange message")
     */
    protected $dataDateRange;

    /**
     * @IsString()
     * @Dns(message="dns message")
     */
    protected $dataDns;

    /**
     * @IsFile()
     * @FileMediaType(mediaType={"image/png"})
     */
    protected $dataFileMediaType;

    /**
     * @IsFile()
     * @FileSize(size=10000)
     */
    protected $dataFileSize;

    /**
     * @IsFile()
     * @FileSuffix(suffix={"png"})
     */
    protected $dataFileSuffix;

    /**
     * @IsFile()
     */
    protected $dataIsFile;

    /**
     * @IsString()
     * @Low(message="low message")
     */
    protected $dataLow;

    /**
     * @IsInt()
     * @NotInEnum(values={1,2,3},message="notinenum message")
     */
    protected $dataNotInEnum;

    /**
     * @IsInt()
     * @NotInRange(min=1,max=3,message="notinrange message")
     */
    protected $dataNotInRange;

    /**
     * @IsString()
     * @Upper(message="upper message")
     */
    protected $dataUpper;

    /**
     * @IsString()
     * @Url(message="url message")
     */
    protected $dataUrl;
}