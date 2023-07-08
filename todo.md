## POST /api/v2/tickets

Takes a ticket object that specifies the ticket properties. The only required property is comment. See Ticket Comments.

### Body

```
//Markdown formatting is supported in body but not in html_body. Example:

{
"ticket": {
"comment": {
"body": "The smoke is very colorful."
},
"priority": "urgent",
"subject": "My printer is on fire!"
}
}
```

### Response

```
// Status 201 Created

{
  "ticket": {
    "assignee_id": 235323,
    "collaborator_ids": [
      35334,
      234
    ],
    "created_at": "2009-07-20T22:55:29Z",
    "custom_fields": [
      {
        "id": 27642,
        "value": "745"
      },
      {
        "id": 27648,
        "value": "yes"
      }
    ],
    "custom_status_id": 123,
    "description": "The fire is very colorful.",
    "due_at": null,
    "external_id": "ahg35h3jh",
    "follower_ids": [
      35334,
      234
    ],
    "from_messaging_channel": false,
    "group_id": 98738,
    "has_incidents": false,
    "id": 35436,
    "organization_id": 509974,
    "priority": "high",
    "problem_id": 9873764,
    "raw_subject": "{{dc.printer_on_fire}}",
    "recipient": "support@company.com",
    "requester_id": 20978392,
    "satisfaction_rating": {
      "comment": "Great support!",
      "id": 1234,
      "score": "good"
    },
    "sharing_agreement_ids": [
      84432
    ],
    "status": "open",
    "subject": "Help, my printer is on fire!",
    "submitter_id": 76872,
    "tags": [
      "enterprise",
      "other_tag"
    ],
    "type": "incident",
    "updated_at": "2011-05-05T10:38:52Z",
    "url": "https://company.zendesk.com/api/v2/tickets/35436.json",
    "via": {
      "channel": "web"
    }
  }
}
```

## POST /api/v2/uploads

### DTO

```
{
  "content_type": "image/png",
  "content_url": "https://company.zendesk.com/attachments/my_funny_profile_pic.png",
  "file_name": "my_funny_profile_pic.png",
  "id": 928374,
  "size": 166144,
  "thumbnails": [
    {
      "content_type": "image/png",
      "content_url": "https://company.zendesk.com/attachments/my_funny_profile_pic_thumb.png",
      "file_name": "my_funny_profile_pic_thumb.png",
      "id": 928375,
      "size": 58298
    }
  ]
}
```

### Response

```
{
  "upload": {
    "token": "4bLLKSOU63CPqaIeOMXYyXzUh",
    "expires_at": "2021-05-08T22:50:18Z",
    "attachment": {
      "url": "https://example.zendesk.com/api/v2/attachments/1503194928902.json",
      "id":1503194928902,
      "file_name": "order_issue.png",
      "content_url": "https://example.zendesk.com/attachments/token/vp7DnuiSvehLZtK2yrPjqJ1l6/?name=order_issue.png",
      "content_type": "image/png"
    },
    "attachments": [
      {
        "url": "https://example.zendesk.com/api/v2/attachments/1503194928902.json",
        "id":1503194928902,
        "file_name": "order_issue.png",
        "content_url": "https://example.zendesk.com/attachments/token/vp7DnuiSvehLZtK2yrPjqJ1l6/?name=order_issue.png",
        "content_type": "image/png"
      }
    ]
  }
}
```

## PUT /api/v2/tickets/45135

```
curl https://example.zendesk.com/api/v2/tickets/45135 \
-d '{"ticket": {"comment": {"body": "Press play", "uploads": ["4bLLKSOU63CPqaIeOMXYyXzUh"]}}}' \
-H "Content-Type: application/json" \
-v -u {email_address}:{password} -X PUT
```
