import { Module } from '@nestjs/common';
import { GsbService } from './gsb.service';
import { GsbController } from './gsb.controller';
import { HttpModule } from '@nestjs/axios';

@Module({
  imports: [HttpModule],
  controllers: [GsbController],
  providers: [GsbService]
})
export class GsbModule {}
