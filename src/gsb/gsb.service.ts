import { ForbiddenException, Injectable } from '@nestjs/common';
import { IReqOtpGsbDto, ISubmitOtpGsbDto } from './dto/create-gsb.dto';
import * as crypto from 'crypto';
import { HttpService } from '@nestjs/axios';
import { AxiosRequestConfig } from 'axios';

@Injectable()
export class GsbService {

  private readonly aesKey: string;
  private readonly aesIv: string;
  private readonly useragent: string;
  private readonly version: string = "2.15.4";
  private readonly endpoint: string = "https://mymo.gsb.or.th:20443";

  constructor(private readonly httpService: HttpService) {
    this.aesKey = 'fc1e360027628363c1178875053668750bfb6ed4cd1cabea0a407ff034105987'; // แทนค่าด้วยคีย์ AES ของคุณ
    this.aesIv = '36453633394331363442344239313938';   // แทนค่าด้วย IV ของคุณ
    this.useragent = `Android10Xiaomi; MyMo254; ${this.version}`;
  }

  decrypt(encData: string): string {
    const baseData = Buffer.from(encData, 'base64');
    const aesKey = Buffer.from(this.aesKey, 'hex');
    const aesIv = Buffer.from(this.aesIv, 'hex');
    const decipher = crypto.createDecipheriv('aes-256-cbc', aesKey, aesIv);
    let res = decipher.update(baseData);
    res = Buffer.concat([res, decipher.final()]);
    return res.toString('utf-8');
  }

  encrypt(encData: string): string {
    const aesKey = Buffer.from(this.aesKey, 'hex');
    const aesIv = Buffer.from(this.aesIv, 'hex');
    const cipher = crypto.createCipheriv('aes-256-cbc', aesKey, aesIv);
    let res = cipher.update(encData, 'utf-8');
    res = Buffer.concat([res, cipher.final()]);
    return res.toString('base64');
  }

  pbkdf2(password: string, salt: string, count: number, keyLength: number): string {
    const derivedKey = crypto.pbkdf2Sync(password, salt, count, keyLength, 'sha1');
    return derivedKey.toString('hex');
  }

  encryptUser(encData: string, seed: string): string {
    const saltValue = `1461200161101${seed}`;
    const derivedKey = this.pbkdf2('0', saltValue, 5, 32);
    const aesKey = derivedKey;
    const aesIv = Buffer.from(this.aesIv, 'hex');
    const cipher = crypto.createCipheriv('aes-256-cbc', aesKey, aesIv);
    let res = cipher.update(encData, 'utf-8');
    res = Buffer.concat([res, cipher.final()]);
    return res.toString('base64');
  }

  decryptUser(encData: string, seed: string): string {
    const baseData = Buffer.from(encData, 'base64');
    const saltValue = `1461200161101${seed}`;
    const derivedKey = this.pbkdf2('0', saltValue, 5, 32);
    const aesKey = derivedKey;
    const aesIv = Buffer.from(this.aesIv, 'hex');
    const decipher = crypto.createDecipheriv('aes-256-cbc', aesKey, aesIv);
    let decryptedData = decipher.update(baseData);
    decryptedData = Buffer.concat([decryptedData, decipher.final()]);
    return decryptedData.toString('utf-8');
  }

  async validateVersion(): Promise<any> {
    const payload = {
      citizenId: '',
      uniqueKey: '',
      version: this.version,
      lang: 'th',
      os: 'Android',
      deviceModel: 'Redmi 8',
      osVersion: '29',
      longitude: '',
      latitude: '',
      isCDNSupported: '1',
    };

    const payloadEnc = this.encrypt(JSON.stringify(payload));

    const header = {
      data: payloadEnc,
    };

    const req = {
      app: 'MyMoGSB',
      dom: 'MyMo',
      op: 'validateVersion',
      sid: '',
      srv: 'MyMoAuthen',
      header: JSON.stringify(header),
    };
    const payloadArray = { req };

    const config = {
      headers: {
        'User-Agent': this.useragent,
      },
    };

    try {
      const response = await this.httpService
        .post(this.endpoint + '/json/MyMoAuthen/validateVersion', payloadArray, config)
        .toPromise();
      return this.decrypt(response.data.res.header.data);
    } catch (error) {
      // จัดการข้อผิดพลาด
      throw new ForbiddenException('เกิดข้อผิดพลาดในการส่งคำขอ HTTP');
    }
  }

  async requestOtp(requestOtpGsbDto: IReqOtpGsbDto): Promise<any> {
    const payload = {
      citizenId: requestOtpGsbDto.citizenId,
      version: this.version,
      lang: 'th',
      os: 'Android',
      deviceModel: 'Redmi 8',
      osVersion: '29',
      longitude: '',
      latitude: '',
      isCDNSupported: '1',
    };

    const payloadEnc = this.encrypt(JSON.stringify(payload));

    const header = {
      data: payloadEnc,
    };

    const req = {
      app: 'MyMoGSB',
      dom: 'MyMo',
      op: 'otpRequest',
      sid: '',
      srv: 'MyMoAuthen',
      header: JSON.stringify(header),
    };

    const config: AxiosRequestConfig = {
      headers: {
        'User-Agent': this.useragent, // ตั้งค่า user-agent string ที่นี่
      },
    };
    const payloadArray = { req };

    try {
      const result = await this.httpService
      .post(this.endpoint + '/json/MyMoAuthen/otpRequest', payloadArray, config)
      .toPromise();
      return this.decrypt(result.data.res.header.data);
    } catch (error) {
      // จัดการข้อผิดพลาด
      throw new ForbiddenException('เกิดข้อผิดพลาดในการส่งคำขอ HTTP');
    }


  }

  async validateOtp(requestOtpGsbDto: ISubmitOtpGsbDto): Promise<any> {
    const payload = {
      citizenId: requestOtpGsbDto.citizenId,
      key: requestOtpGsbDto.otp,
      version: this.version,
      lang: 'th',
      os: 'Android',
      deviceModel: 'Redmi 8',
      osVersion: '29',
      longitude: '',
      latitude: '',
      isCDNSupported: '1',
    };

    const payloadEnc = this.encrypt(JSON.stringify(payload));

    const header = {
      data: payloadEnc,
    };

    const req = {
      app: 'MyMoGSB',
      dom: 'MyMo',
      op: 'validateOtp',
      sid: '',
      srv: 'MyMoAuthen',
      header: JSON.stringify(header),
    };

    const config: AxiosRequestConfig = {
      headers: {
        'User-Agent': this.useragent, // ตั้งค่า user-agent string ที่นี่
      },
    };

    const payloadArray = { req };


    try {
      const result = await this.httpService
      .post(this.endpoint + '/json/MyMoAuthen/validateOtp', payloadArray, config)
      .toPromise();
      return this.decrypt(result.data.res.header.data);
    } catch (error) {
      // จัดการข้อผิดพลาด
      throw new ForbiddenException('เกิดข้อผิดพลาดในการส่งคำขอ HTTP');
    }
  }

}
