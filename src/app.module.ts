import { Module } from '@nestjs/common';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { GsbModule } from './gsb/gsb.module';

@Module({
  imports: [GsbModule],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}
