package jwt

import (
    "github.com/dgrijalva/jwt-go"
    "github.com/gogf/gf/g"
    "time"
    "fmt"
)

func CreateToken(key string, m map[string] interface{}) string {
    token := jwt.New(jwt.SigningMethodHS256)
    claims := make(jwt.MapClaims)
    for index, val := range m {
        claims[index] = val
    }
    claims["exp"] = time.Now().Add(time.Hour * 72).Unix() //  strconv.FormatInt(t.UTC().UnixNano(), 10)
    claims["iat"] = "0"
    token.Claims = claims
    tokenString, _ := token.SignedString([]byte(key))
    return tokenString
}

func ParseToken(tokenString string)(map[string] interface{}, bool)  {
    config := g.Config()
    key := config.GetString("setting.jwt_token")
    token, err := jwt.Parse(tokenString, func(token *jwt.Token) (interface{}, error) {
        if _, ok := token.Method.(*jwt.SigningMethodHMAC); !ok {
            return nil,fmt.Errorf("Unexpected signing method: %v", token.Header["alg"])
        }
        return []byte(key), nil
    })
    if err == nil {
        claims, ok := token.Claims.(jwt.MapClaims)
        if ok && token.Valid {
            var m = make(map[string] interface{})
            for key,val := range  claims {
                m[key] = val
            }
            return m,true
        }
    }
    return nil, false
}


