# magicmirror API描述


---

## /img:
Request: 
**method="POST"**
[file]   key="img"
[string] key="openid"
Response:
[JSON]  facedata OR err msg.

## /pk:

Request:
**method="POST"**
[file]   key="pk"
[string] key="openid"


## /goods:
Request:
**method="GET"**
[string] key="facestep"
[string] key="facetype"

Response:
[JSON] `No item` OR DATA

## /status:
Request:
**method="POST"**
[string] key="openid"
[int] key="statu"  
(Note: `statu` Value Union:-,0,+)

Response:
[JSON] `access success` OR `access denied`

