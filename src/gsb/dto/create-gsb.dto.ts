import { ApiProperty } from "@nestjs/swagger";

export class IReqOtpGsbDto {
    @ApiProperty({
        type:String,
        example: '123456789',
        nullable: true,
    })
    citizenId: string;
}
export class ISubmitOtpGsbDto {
    @ApiProperty({
        type:String,
        example: '123456789',
        nullable: true,
    })
    citizenId: string;

    @ApiProperty({
        type:String,
        example: '123456',
        nullable: true,
    })
    otp: string;
}

