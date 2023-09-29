import { Controller, Get, Post, Body, Patch, Param, Delete, HttpCode } from '@nestjs/common';
import { GsbService } from './gsb.service';
import { IReqOtpGsbDto, ISubmitOtpGsbDto } from './dto/create-gsb.dto';

@Controller('gsb')
export class GsbController {
  constructor(private readonly gsbService: GsbService) {}

  @HttpCode(200)
  @Post('req/otp')
  requestOtp(@Body() requestOtpGsbDto: IReqOtpGsbDto) {
    return this.gsbService.requestOtp(requestOtpGsbDto);
  }

  @Post('submit/otp')
  submitOtp(@Body() requestOtpGsbDto: ISubmitOtpGsbDto) {
    return this.gsbService.validateOtp(requestOtpGsbDto);
  }

  @Get('validateVersion')
  validateVersion() {
    return this.gsbService.validateVersion();
  }

}
