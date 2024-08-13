require 'sinatra'
require 'json'

post '/enter_sweepstakes' do
  data = JSON.parse(request.body.read)
  name = data['name']
  email = data['email']
  phone = data['phone']
  terms = data['terms']

  if terms
    # Save data to the database or file
    status 200
    body "Entry received!"
  else
    status 400
    body "You must agree to the terms and conditions."
  end
end
