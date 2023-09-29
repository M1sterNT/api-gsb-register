import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);
  const config = new DocumentBuilder()
  .setTitle('GSB REGISTER API')
  .setDescription('GSB  REGISTER API description')
  .setVersion('1.0')
  .addBearerAuth({ type: 'http', scheme: 'bearer', bearerFormat: 'Token' })
  .build();
const document = SwaggerModule.createDocument(app, config);
SwaggerModule.setup('/api/docsapi', app, document);
  await app.listen(3001);
}
bootstrap();
