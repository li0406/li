import axios from 'axios';

describe('Utils:test', () => {
  it('返回200', async () => {
    const result = await axios.get('http://www.mocky.io/v2/5ea2b86831000048db1ef376');
    expect(result.data.status).toBe(200);
  });
});
