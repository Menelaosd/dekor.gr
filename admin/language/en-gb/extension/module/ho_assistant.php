<?php
// Heading
$_['heading_title']      = 'HO Shopping Assistant (AI chat)';

// Text
$_['text_extension']     = 'Extensions';
$_['text_success']       = 'The assistant settings are saved.';
$_['text_edit']          = 'Assistant settings';
$_['text_switch_off']    = 'The assistant is off for this store. It is switched on only in system/config/happyonline.php (ho_assistant_status = true). Settings are saved as usual.';
$_['text_switch_on']     = 'The assistant is on and shows bottom right on every page of DEKOR and APOKRIFA (not on the checkout). To test it with one customer only, enter their e-mail below.';
$_['text_no_key']        = 'The key for the chosen model is missing (OpenAI for gpt, Anthropic for claude): add it below. Until then the chat does not show.';
$_['text_intro']         = 'A chat at the bottom right of every page: it answers about products, prices, stock, shipping and payment from the store\'s real data, and completes orders with a form inside the chat. The customer\'s personal details are never sent to OpenAI.';
$_['text_tab_general']   = 'General';
$_['text_tab_texts']     = 'Texts';
$_['text_tab_orders']    = 'Orders & limits';
$_['text_tab_log']       = 'Conversations';
$_['text_today']         = 'Today';
$_['text_month']         = 'This month';
$_['text_requests']      = 'messages';
$_['text_cost']          = 'cost (approx.)';
$_['text_orders']        = 'orders from the chat';
$_['text_key_saved']     = 'Saved key: %s';
$_['text_key_file']      = 'The key comes from system/config/happyonline.php (ho_assistant_openai_key: %s) and takes precedence over this one.';
$_['text_key_remove']    = 'Delete the key';
$_['text_offline']       = 'no bank page';
$_['text_online']        = 'with a payment page: from the chat the customer goes to the bank\'s page and back to the store';
$_['entry_only_customer'] = 'Only for the customer';
$_['help_only_customer']  = 'For tests on the live store: enter the e-mail of a customer account and the assistant shows ONLY while that customer is logged in. Empty: everyone sees it.';
$_['text_only_customer_on']   = 'Test mode: the assistant shows only to the customer %s (while logged in).';
$_['error_only_customer']     = 'No customer has this e-mail: nobody sees the assistant.';
$_['text_no_chats']      = 'No conversations yet.';
$_['text_view']          = 'View';
$_['text_order']         = 'Order #%s';
$_['text_customer']      = 'Customer';
$_['text_assistant']     = 'Assistant';
$_['text_cards']         = '(+ %s cards)';
$_['text_mock']          = 'Test without OpenAI (mock, no cost)';

// Entry
$_['entry_status']       = 'Status';
$_['entry_key']          = 'OpenAI API key';
$_['entry_model']        = 'Model';
$_['entry_name']         = 'Assistant name';
$_['entry_greeting']     = 'Greeting';
$_['entry_suggestions']  = 'Suggested questions (one per line, up to 4)';
$_['entry_instructions'] = 'The store\'s instructions';
$_['entry_budget']       = 'Daily cost limit ($)';
$_['entry_rate']         = 'Messages per visitor per hour';
$_['entry_payments']     = 'Payments completed inside the chat';
$_['entry_retention']    = 'Keep conversations (days)';

// Help
$_['help_key']           = 'From platform.openai.com › API keys. Stored encrypted and never shown again. Leave empty to keep the current one.';
$_['help_model']         = 'gpt-4.1-mini is fast and cheap, enough for product questions. claude-haiku-5-5 is cheaper still and fast; claude-sonnet-5-5 is more capable and costs more. Claude models need an Anthropic key, the others an OpenAI key.';
$_['entry_anthropic_key'] = 'Anthropic API key (Claude)';
$_['help_anthropic_key']  = 'Only for the Claude models. From platform.claude.com › API keys. Stored encrypted and never shown again. Leave empty to keep the current one.';
$_['text_anthropic_key_file'] = 'The Anthropic key comes from a config file (ho_assistant_anthropic_key: %s) and takes precedence.';
$_['help_instructions']  = 'E.g. «Suggest products on sale first». They never change the core rules (prices only from the store, no personal details in the chat).';
$_['help_budget']        = 'Once reached, the assistant says it is unavailable until the next day. Check the limits in your OpenAI account too.';
$_['help_texts']         = 'Empty = the language\'s default text.';
$_['help_retention']     = 'Conversations may hold what the customer typed. Keep them only as long as needed (GDPR).';

// Store profile
$_['entry_profile']         = 'Store profile';
$_['button_profile']        = 'Build the profile from the store';
$_['button_profile_again']  = 'Rebuild from the current data';
$_['text_profile_empty']    = 'Not built yet: press the button the first time.';
$_['text_profile_placeholder'] = 'Press «Build the profile»: the assistant reads categories, brands, prices, best sellers, options (size, colour…), pages, shipping and payment, and writes here what it must know about this store.';
$_['text_profile_confirm']  = 'The current profile will be replaced by a new one. Continue?';
$_['text_profile_working']  = 'Reading the store and writing the profile… (up to a minute)';
$_['text_profile_done_ai']  = 'Ready and saved. Read it and correct anything you like (then «Save»).';
$_['text_profile_done_data'] = 'Saved with the store data. With an OpenAI key and a real model, the same button writes a complete profile with AI.';
$_['text_profile_ai_failed'] = 'The AI did not answer (%s).';
$_['text_profile_failed']   = 'It did not complete. Please try again.';
$_['text_profile_data_intro'] = '### Store data (without AI)
The assistant uses it as it is. Press the button again with an OpenAI key for a full profile.';
$_['help_profile']          = 'What the assistant must know about THIS store: what it sells, to whom, in what tone, where to look for each wish, how to recommend. The core rules (prices only from the store, the order form, personal data) are fixed and cannot be changed here. Rebuild it when the catalogue changes a lot.';

// Error
$_['error_permission']   = 'Warning: You do not have permission to modify the assistant!';
