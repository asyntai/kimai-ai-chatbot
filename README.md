# Asyntai AI Chatbot for Kimai

An AI chat assistant on your Kimai. **[Asyntai](https://asyntai.com)** answers your users all day, in their language, from the instructions and the knowledge you give it.

[Asyntai](https://asyntai.com) · [Documentation](https://asyntai.com/documentation/integrations/kimai/)

## What it does

- **Chat assistant on your pages.** Connect your Kimai to an Asyntai account with one button in the plugin settings. The chat widget appears for signed-in users and on the login page. You choose who sees it.
- **Any language.** Users write in their own language and the assistant answers in it.
- **Your own answers.** Set up the instructions, the knowledge and the look of the assistant in the Asyntai panel. No change in Kimai is necessary.

## Requirements

- Kimai 2.44 or newer (tested on 2.71.0)
- An Asyntai account. You can create a free account from the plugin settings.

## Installation

1. Download `AsyntaiChatbotBundle-1.0.0.zip` from the [latest release](https://github.com/asyntai/kimai-ai-chatbot/releases/latest).
2. Extract it to `var/plugins/`, so that this path exists: `var/plugins/AsyntaiChatbotBundle/AsyntaiChatbotBundle.php`. The folder name must stay `AsyntaiChatbotBundle`.
3. Reload Kimai:

```bash
bin/console kimai:reload
```

Or with Git:

```bash
cd var/plugins/ && git clone https://github.com/asyntai/kimai-ai-chatbot.git AsyntaiChatbotBundle && cd ../.. && bin/console kimai:reload
```

## Setup

1. Sign in to Kimai as a user with the `system_configuration` permission, for example a super administrator.
2. Open **System → Asyntai AI Chatbot** and press **Get started**.
3. A window opens where you sign in to Asyntai, or create an account. When it closes, the widget is live.
4. Under **Where the chat shows**, tick signed-in users, the login page, or both, and press **Save**.

## Uninstall

Remove the `var/plugins/AsyntaiChatbotBundle` folder and run `bin/console kimai:reload`.

## Support

hello@asyntai.com

## License

MIT
