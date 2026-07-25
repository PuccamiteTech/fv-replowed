# Flash client notes

This document records client behavior verified from the shipped FarmVille Flash
client. It is intended to keep the PHP AMF service responses aligned with the
client without committing a full decompiler export.

## Inspecting a client build

The observations below were made from:

- File: `public/farmville/embeds/Flash/v855037.855026/FarmGame.855037.855026.swf`
- SHA-256: `CC8BBDADDD7E6CD8F0D0ADA79F078E442539A78F51E24138D1F2240C51D5380D`
- Decompiler: JPEXS Free Flash Decompiler 26.2.1

When checking another build, decompile the specific class involved and record
the SWF version and hash alongside the result. Decompiled sources are generated
artifacts and should not be committed by default.

## AMF callback transport rule

The generic transaction flow is important for every entry below:

```actionscript
// Engine.Transactions.Transaction.onAmfComplete
if (param1.errorType == NO_ERROR && param1.data != null) {
    this.onComplete(param1.data);
}
```

`result` in a transaction callback is therefore the handler's returned
`data` object. Service-specific callback fields must be placed inside `data`;
the surrounding AMF fields such as `errorType`, `sequenceNumber`, and
`worldTime` are not part of the callback payload.

## `WatchToEarnRewardGrantService.getUserZid`

The client calls this service during `Transactions.TPostInit.getUserZid()`:

```actionscript
TransactionManager.addTransaction(new TGenericTransaction(
    "WatchToEarnRewardGrantService",
    "getUserZid",
    this.onGetUserZid
));
```

Its completion callback reads `success` and `zid` at the top level of its
`result` payload:

```actionscript
public function onGetUserZid(result:Object) : void {
    if (result.success) {
        if (result.zid) {
            ExternalUtil.instance.call("FarmNS.setZid", result.zid.toString());
        }
    }
}
```

The server must therefore return at least:

```php
return [
    'data' => [
        'success' => true,
        'zid' => (string) $playerObj->getUid(),
    ],
];
```

Consequently, putting `success` or `zid` beside the returned `data` object does
**not** satisfy this callback. The current PHP handler does not match this
contract.

## `WatchToEarnRewardGrantService.generateDailyTokens`

`Classes.WatchToEarnManager.onGenerateDailyToken` reads these fields from its
callback result:

```actionscript
if (result.success) {
    if (result["Tokens"].length > 0) {
        // stores each returned daily token
    }
}
```

The minimum successful no-token response is therefore:

```php
return [
    'data' => [
        'success' => true,
        'Tokens' => [],
    ],
];
```

Field spelling is significant: the client reads `Tokens` with a capital `T`.
The existing handler's `tokens` field and missing `success` field do not match
this verified callback contract.

## Startup response contracts

`Transactions.TInitUser` calls `UserService.initUser`; its `onComplete` method
receives the `data` object and reads a large user-state payload directly from
it, including `player`, `userInfo`, `world`, `attr`, `experiments`, and
`flashHotParams`. This is a high-risk contract: new or modified fields should
be verified in `TInitUser.onComplete` before changing `Player::getData()`.

`Transactions.TPostInit` calls `UserService.postInit`; its callback likewise
receives `data` and reads optional feature-state fields such as `w2wState`,
`avatarState`, `hudIcons`, `fcSlotMachineRewards`, `bestSellers`, and
`lotteryData`. Missing optional fields are generally guarded, but a field's
shape still needs verification in `TPostInit.onComplete` before changing it.
