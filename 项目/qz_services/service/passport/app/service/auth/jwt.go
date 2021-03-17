package auth

import (
	"fmt"
	"github.com/dgrijalva/jwt-go"
	"github.com/gogf/gf/frame/g"
	"github.com/gogf/gf/util/gconv"
	"log"
	"time"
)

var JWT_CONFIG map[string]interface{}

func init() {
	JWT_CONFIG = g.Config().GetMap("jwt")

	if _, ok := JWT_CONFIG["key"]; !ok {
		log.Fatal("未发现jwt配置项key")
	}
	if _, ok := JWT_CONFIG["expire"]; !ok {
		log.Fatal("未发现jwt配置项expire")
	}
	if _, ok := JWT_CONFIG["refresh_expire"]; !ok {
		log.Fatal("未发现jwt配置项refresh_expire")
	}
}

type PassportJwt struct {
	PassportClaims PassportClaims
}

type PassportClaims struct {
	Uuid           int64  `json:"uuid"`       //uuid
	Phone          string `json:"phone"`      //手机号码
	Addr           string `json:"addr"`       //来源缩写,qz_ucenter_platform表中的addr
	Device         string `json:"device"`     //载体,IOS/ANDROID/WEB
	Refresh_expire int64  `json:"re_exp"`     //过期可刷新时间
	TokenType      int    `json:"token_type"` //token类型
	ExpiresAt      int64  `json:"exp"`        //过期时间
	//Random         int    `json:"random"`     //随机数
	//Timestamp      int64  `json:"timestamp"`  //时间戳
	jwt.StandardClaims
}

func NewPassportJwt() *PassportJwt {
	return &PassportJwt{
		PassportClaims: NewPassportClaims(),
	}
}

func NewPassportClaims() PassportClaims {
	return PassportClaims{
		Refresh_expire: time.Now().Unix() + gconv.Int64(JWT_CONFIG["refresh_expire"])*60*60,
		ExpiresAt:      time.Now().Unix() + gconv.Int64(JWT_CONFIG["expire"])*60*60,
		//Random:         rand.Int(),
		//Timestamp:      time.Now().Unix(),
		TokenType: 1,
		StandardClaims: jwt.StandardClaims{
			//ExpiresAt: time.Now().Unix() + gconv.Int64(JWT_CONFIG["expire"])*60*60,
			Issuer:   "qizuang.com",
			IssuedAt: time.Now().Unix(),
		},
	}
}

//1.生成
func (j *PassportJwt) Create() (string, error) {
	signKey := gconv.Bytes(JWT_CONFIG["key"])
	claims := j.PassportClaims
	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	tok, err := token.SignedString(signKey)
	if err != nil {
		err := fmt.Errorf("签发生成jwt失败,%w", err)
		return "", err
	}
	return tok, nil
}

// 转码
func (j *PassportJwt) Parse(token string) (*PassportClaims, error) {
	tok, err := jwt.ParseWithClaims(token, &PassportClaims{}, func(tok *jwt.Token) (interface{}, error) {
		return gconv.Bytes(JWT_CONFIG["key"]), nil
	})

	if claims, ok := tok.Claims.(*PassportClaims); ok && tok.Valid {
		return claims, nil
	}
	return &PassportClaims{}, err
}

// 转码
func (j *PassportJwt) GetSeeder() string {
	return fmt.Sprintf("%d%s%s", j.PassportClaims.Uuid, j.PassportClaims.Addr, j.PassportClaims.Device)
}
